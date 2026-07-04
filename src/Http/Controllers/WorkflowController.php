<?php

namespace Mrgiant\FormBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Mrgiant\FormBuilder\Services\WorkflowRunner;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Models\FormWorkflowEdge;
use Mrgiant\FormBuilder\Models\FormWorkflowNode;
use App\Models\User;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowRunner $runner) {}

    public function workflowPage(Form $form)
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.forms.workflow', compact('form'));
    }

    /**
     * Nodes + edges for the visual builder, plus the form's questions (for condition fields).
     */
    public function getWorkflow(Form $form): JsonResponse
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $nodes = $form->workflowNodes()->with('users:id,name')->get()->map(fn (FormWorkflowNode $n) => [
            'id' => $n->id,
            'type' => $n->type,
            'name' => $n->name,
            'mode' => $n->mode,
            'config' => $n->config ?? [],
            'pos_x' => $n->pos_x,
            'pos_y' => $n->pos_y,
            'users' => $n->users->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name])->values(),
        ]);

        $edges = $form->workflowEdges()->get()->map(fn (FormWorkflowEdge $e) => [
            'from' => $e->from_node_id,
            'to' => $e->to_node_id,
            'branch' => $e->branch,
        ]);

        $questions = $form->questions()->get(['id', 'label', 'question_type'])
            ->map(fn ($q) => ['id' => $q->id, 'label' => $q->label]);

        return response()->json(['nodes' => $nodes, 'edges' => $edges, 'questions' => $questions]);
    }

    /**
     * Replace the whole graph. Nodes carry a client "key"; edges reference those keys
     * (null key = Start / End).
     */
    public function saveWorkflow(Request $request, Form $form): JsonResponse
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $this->validate($request, [
            'nodes' => 'present|array',
            'nodes.*.key' => 'required',
            'nodes.*.type' => 'required|in:approval,notification,condition,update,terminate,http',
            'nodes.*.name' => 'required|string|max:255',
            'nodes.*.mode' => 'nullable|in:any,all',
            'nodes.*.config' => 'nullable|array',
            'nodes.*.pos_x' => 'nullable|integer',
            'nodes.*.pos_y' => 'nullable|integer',
            'nodes.*.users' => 'nullable|array',
            'nodes.*.users.*' => 'integer|exists:users,id',
            'edges' => 'present|array',
            'edges.*.from' => 'nullable',
            'edges.*.to' => 'nullable',
            'edges.*.branch' => 'required|in:next,true,false',
        ]);

        $form->workflowEdges()->delete();
        $form->workflowNodes()->delete();

        $map = [];
        foreach ($validated['nodes'] as $item) {
            $node = FormWorkflowNode::create([
                'form_id' => $form->id,
                'type' => $item['type'],
                'name' => $item['name'],
                'mode' => $item['mode'] ?? null,
                'config' => $item['config'] ?? [],
                'pos_x' => $item['pos_x'] ?? null,
                'pos_y' => $item['pos_y'] ?? null,
            ]);
            $map[(string) $item['key']] = $node->id;

            if (! empty($item['users'])) {
                $node->users()->sync(array_values(array_unique($item['users'])));
            }
        }

        foreach ($validated['edges'] as $edge) {
            FormWorkflowEdge::create([
                'form_id' => $form->id,
                'from_node_id' => $edge['from'] !== null ? ($map[(string) $edge['from']] ?? null) : null,
                'to_node_id' => $edge['to'] !== null ? ($map[(string) $edge['to']] ?? null) : null,
                'branch' => $edge['branch'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function approverUsers(): JsonResponse
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::query()->where('status', 1)->orderBy('name')->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name]);

        return response()->json(['users' => $users]);
    }

    public function tracker($form_id, FormResponse $response): JsonResponse
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $form = $response->form;
        $workflow = $response->workflow;
        $actions = $response->workflowActions()->with('user:id,name')->get();

        $nodes = $form->workflowNodes()->with('users:id,name')->get()->map(function (FormWorkflowNode $node) use ($workflow, $actions) {
            $nodeActions = $actions->where('node_id', $node->id);
            $status = 'upcoming';

            if ($nodeActions->firstWhere('action', 'rejected')) {
                $status = 'rejected';
            } elseif ($workflow && $workflow->current_node_id === $node->id && $workflow->status === 'pending') {
                $status = 'current';
            } elseif ($nodeActions->isNotEmpty()) {
                $status = 'done';
            }

            return [
                'id' => $node->id,
                'type' => $node->type,
                'name' => $node->name,
                'mode' => $node->mode,
                'config' => $node->config ?? [],
                'pos_x' => $node->pos_x,
                'pos_y' => $node->pos_y,
                'status' => $status,
                'users' => $node->users->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name])->values(),
                'actions' => $nodeActions->map(fn ($a) => [
                    'user' => optional($a->user)->name,
                    'action' => $a->action,
                    'comment' => $a->comment,
                    'acted_at' => optional($a->acted_at)->format('Y-m-d H:i'),
                ])->values(),
            ];
        });

        $edges = $form->workflowEdges()->get()->map(fn (FormWorkflowEdge $e) => [
            'from' => $e->from_node_id, 'to' => $e->to_node_id, 'branch' => $e->branch,
        ]);

        return response()->json([
            'approval_status' => $response->approval_status,
            'status' => $workflow?->status ?? 'not_required',
            'pending_with' => $this->runner->currentApprovers($response)->map(fn (User $u) => $u->name)->values(),
            'nodes' => $nodes,
            'edges' => $edges,
        ]);
    }

    public function inboxList(Request $request): JsonResponse
    {
        $user = $request->user();

        $candidates = FormResponse::query()
            ->where('approval_status', 'pending')
            ->whereExists(function ($q) use ($user) {
                $q->selectRaw('1')
                    ->from('gl_form_response_workflows as w')
                    ->join('gl_form_workflow_nodes as n', 'n.id', '=', 'w.current_node_id')
                    ->join('gl_form_workflow_node_users as nu', 'nu.node_id', '=', 'n.id')
                    ->whereColumn('w.form_response_id', 'gl_form_responses.id')
                    ->where('w.status', 'pending')
                    ->where('n.type', 'approval')
                    ->where('nu.user_id', $user->id);
            })
            ->with('form:id,name', 'workflow.currentNode:id,name')
            ->latest()
            ->get();

        $items = $candidates
            ->filter(fn (FormResponse $r) => $this->runner->canAct($r, $user))
            ->map(fn (FormResponse $r) => [
                'id' => $r->id,
                'form_id' => $r->form_id,
                'form_name' => optional($r->form)->name,
                'submitted_at' => optional($r->created_at)->format('Y-m-d H:i'),
                'step_name' => optional(optional($r->workflow)->currentNode)->name,
            ])
            ->values();

        return response()->json(['items' => $items]);
    }

    public function inbox()
    {
        return view('admin.approvals.inbox');
    }

    public function approve(Request $request, FormResponse $response): JsonResponse
    {
        return $this->act($request, $response, 'approve');
    }

    public function reject(Request $request, FormResponse $response): JsonResponse
    {
        return $this->act($request, $response, 'reject');
    }

    private function act(Request $request, FormResponse $response, string $action): JsonResponse
    {
        $user = $request->user();

        abort_if(! $this->runner->canAct($response, $user), Response::HTTP_FORBIDDEN, 'You cannot act on this response.');

        $comment = $request->input('comment');

        if ($action === 'approve') {
            $this->runner->approve($response, $user, $comment);
        } else {
            $this->runner->reject($response, $user, $comment);
        }

        return response()->json(['success' => true, 'approval_status' => $response->fresh()->approval_status]);
    }
}
