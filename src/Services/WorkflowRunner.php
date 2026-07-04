<?php

namespace Mrgiant\FormBuilder\Services;

use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Models\FormResponseWorkflow;
use Mrgiant\FormBuilder\Models\FormResponseWorkflowAction;
use Mrgiant\FormBuilder\Models\FormWorkflowEdge;
use Mrgiant\FormBuilder\Models\FormWorkflowNode;
use App\Models\User;
use Mrgiant\FormBuilder\Notifications\FormWorkflowNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Runs a form's workflow graph for a submitted response:
 * notification & condition nodes execute instantly and follow their edge;
 * approval nodes pause the run until an assigned user approves/rejects.
 */
class WorkflowRunner
{
    public function formHasWorkflow(Form $form): bool
    {
        return $form->workflowNodes()->exists();
    }

    /**
     * Kick off the workflow for a freshly submitted response.
     */
    public function start(FormResponse $response): void
    {
        $form = $response->form;
        $entry = $this->entryNode($form);

        if (! $this->formHasWorkflow($form) || ! $entry) {
            $response->approval_status = 'not_required';
            $response->save();

            return;
        }

        FormResponseWorkflow::create([
            'form_response_id' => $response->id,
            'status' => 'pending',
            'current_node_id' => null,
        ]);

        $response->approval_status = 'pending';
        $response->save();

        $this->advanceFrom($response, $entry);
    }

    /**
     * The node the workflow enters first (target of the edge that starts at "Start").
     */
    public function entryNode(Form $form): ?FormWorkflowNode
    {
        $edge = $form->workflowEdges()->whereNull('from_node_id')->first();

        return $edge && $edge->to_node_id ? $form->workflowNodes()->find($edge->to_node_id) : null;
    }

    /**
     * Walk the graph from $node, running auto-nodes and pausing at approvals.
     */
    protected function advanceFrom(FormResponse $response, ?FormWorkflowNode $node): void
    {
        // Guard against misconfigured graphs (cycles of auto-nodes would loop forever).
        $visited = [];
        $guard = 0;

        while ($node) {
            if (in_array($node->id, $visited, true) || ++$guard > 200) {
                Log::warning('Workflow stopped (cycle or step limit) for response '.$response->id.' at node '.$node->id.'.');

                return;
            }
            $visited[] = $node->id;

            if ($node->type === 'notification') {
                $this->runNotification($response, $node);
                $node = $this->nextNode($node, 'next');

                continue;
            }

            if ($node->type === 'condition') {
                $result = $this->evaluateCondition($response, $node);
                $this->log($response, $node, null, $result ? 'condition_true' : 'condition_false');
                $node = $this->nextNode($node, $result ? 'true' : 'false');

                continue;
            }

            if ($node->type === 'update') {
                $this->runUpdate($response, $node);
                $node = $this->nextNode($node, 'next');

                continue;
            }

            if ($node->type === 'http') {
                $this->runHttp($response, $node);
                $node = $this->nextNode($node, 'next');

                continue;
            }

            if ($node->type === 'terminate') {
                $this->runTerminate($response, $node);

                return;
            }

            // approval Ã¢â‚¬â€ pause here.
            $workflow = $response->workflow;
            $workflow->current_node_id = $node->id;
            $workflow->status = 'pending';
            $workflow->save();

            $response->approval_status = 'pending';
            $response->save();

            $this->notifyCurrentApprovers($response);

            return;
        }

        // Ran out of nodes (End node or an unconnected leaf) Ã¢â‚¬â€ finish the workflow.
        $this->complete($response);
    }

    /**
     * Finish the run. Keeps the status the response already holds (e.g. set by an Update
     * node); if it is still 'pending', defaults to 'approved'.
     */
    protected function complete(FormResponse $response, ?string $status = null): void
    {
        $status ??= in_array($response->approval_status, ['approved', 'rejected', 'terminated'], true)
            ? $response->approval_status
            : 'approved';

        $workflow = $response->workflow;
        $workflow->status = $status;
        $workflow->current_node_id = null;
        $workflow->save();

        $response->approval_status = $status;
        $response->save();

        $this->notifySubmitter($response, $status);
    }

    /**
     * Follow the edge leaving $node on the given branch. Returns null at the End.
     */
    protected function nextNode(FormWorkflowNode $node, string $branch): ?FormWorkflowNode
    {
        $edge = FormWorkflowEdge::query()
            ->where('from_node_id', $node->id)
            ->where('branch', $branch)
            ->first();

        if (! $edge) {
            return null;
        }

        return $edge->to_node_id ? FormWorkflowNode::find($edge->to_node_id) : null;
    }

    // ---- node executors -----------------------------------------------------

    protected function runNotification(FormResponse $response, FormWorkflowNode $node): void
    {
        $message = $node->config['message'] ?? null;

        $node->users->each(fn (User $user) => $user->notify(
            new FormWorkflowNotification($response, 'custom', $node->name, $message)
        ));

        $this->log($response, $node, null, 'notified');
    }

    /**
     * Update data on the response Ã¢â‚¬â€ either an answer's value or the approval status Ã¢â‚¬â€ then continue.
     */
    protected function runUpdate(FormResponse $response, FormWorkflowNode $node): void
    {
        $config = $node->config ?? [];
        $target = $config['target'] ?? 'approval_status';

        if ($target === 'answer') {
            foreach ($this->updateFields($config) as $update) {
                $response->answers()->updateOrCreate(
                    ['question_id' => $update['field']],
                    ['value' => $update['value']],
                );
            }
        } elseif ($target === 'approval_status' && ! empty($config['status'])) {
            $response->approval_status = $config['status'];
            $response->save();
        }

        $this->log($response, $node, null, 'updated', $config['message'] ?? null);
    }

    /**
     * Normalize an update node's field/value pairs, supporting both the multi-field
     * shape and the legacy single field/value config.
     *
     * @param  array<string, mixed>  $config
     * @return array<int, array{field: mixed, value: string}>
     */
    protected function updateFields(array $config): array
    {
        $updates = isset($config['updates']) && is_array($config['updates'])
            ? $config['updates']
            : [['field' => $config['field'] ?? null, 'value' => $config['value'] ?? '']];

        return collect($updates)
            ->filter(fn ($update) => is_array($update) && ! empty($update['field']))
            ->map(fn (array $update) => ['field' => $update['field'], 'value' => (string) ($update['value'] ?? '')])
            ->values()
            ->all();
    }

    /**
     * Fire an outbound HTTP request using Laravel's HTTP client, then continue.
     * Failures are logged and never block the workflow.
     */
    protected function runHttp(FormResponse $response, FormWorkflowNode $node): void
    {
        $config = $node->config ?? [];
        $response->loadMissing('answers');

        $url = trim($this->renderTemplate((string) ($config['url'] ?? ''), $response));

        if ($url === '') {
            $this->log($response, $node, null, 'http_skipped', 'No URL configured.');

            return;
        }

        $method = strtoupper($config['method'] ?? 'GET');
        $timeout = (int) ($config['timeout'] ?? 30);
        if ($timeout < 1) {
            $timeout = 30;
        }

        $request = Http::timeout($timeout)
            ->withHeaders($this->httpPairs($config['headers'] ?? [], $response));

        $auth = $config['auth'] ?? [];
        $authType = $auth['type'] ?? 'none';

        if ($authType === 'basic') {
            $request = $request->withBasicAuth((string) ($auth['username'] ?? ''), (string) ($auth['password'] ?? ''));
        } elseif ($authType === 'bearer' && ! empty($auth['token'])) {
            $request = $request->withToken($this->renderTemplate((string) $auth['token'], $response));
        }

        $options = [];

        if ($query = $this->httpPairs($config['query'] ?? [], $response)) {
            $options['query'] = $query;
        }

        $bodyType = $config['body_type'] ?? 'none';

        if ($bodyType === 'json') {
            $options['json'] = $this->httpPairs($config['body'] ?? [], $response);
        } elseif ($bodyType === 'form') {
            $options['form_params'] = $this->httpPairs($config['body'] ?? [], $response);
        } elseif ($bodyType === 'answers') {
            $options['json'] = $this->allAnswersPayload($response, $config['answers_key'] ?? 'id');
        } elseif ($bodyType === 'raw') {
            $options['body'] = $this->renderTemplate((string) ($config['raw_body'] ?? ''), $response);
        }

        try {
            $result = $request->send($method, $url, $options);
            $this->log($response, $node, null, 'http_called', $method.' '.$url.' Ã¢â€ â€™ '.$result->status());
        } catch (\Throwable $e) {
            Log::warning('Workflow HTTP node failed for response '.$response->id.': '.$e->getMessage());
            $this->log($response, $node, null, 'http_failed', $method.' '.$url.' Ã¢â€ â€™ '.$e->getMessage());
        }
    }

    /**
     * The response's full answer set plus its ids, used when an HTTP node is set
     * to send all answers. Keys are the question label ($keyBy = 'label') or
     * "q-<questionId>" (default), falling back to the id when a label is missing.
     *
     * @return array{response_id: int, form_id: int, form_name: ?string, answers: array<string, mixed>}
     */
    protected function allAnswersPayload(FormResponse $response, string $keyBy = 'id'): array
    {
        $response->loadMissing('answers.question', 'form:id,name');

        $answers = [];

        foreach ($response->answers as $answer) {
            $decoded = json_decode((string) $answer->value, true);
            $value = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                ? $decoded
                : $answer->value;

            $label = optional($answer->question)->label;
            $key = $keyBy === 'label' && ! empty($label) ? $label : 'q-'.$answer->question_id;

            $answers[$key] = $value;
        }

        return [
            'response_id' => $response->id,
            'form_id' => $response->form_id,
            'form_name' => optional($response->form)->name,
            'answers' => $answers,
        ];
    }

    /**
     * Convert a list of {key, value} pairs into an associative array, dropping
     * blank keys and resolving {{q-ID}} / {{response_id}} placeholders in values.
     *
     * @param  array<int, mixed>  $pairs
     * @return array<string, string>
     */
    protected function httpPairs(array $pairs, FormResponse $response): array
    {
        $out = [];

        foreach ($pairs as $pair) {
            if (! is_array($pair)) {
                continue;
            }

            $key = trim((string) ($pair['key'] ?? ''));

            if ($key === '') {
                continue;
            }

            $out[$key] = $this->renderTemplate((string) ($pair['value'] ?? ''), $response);
        }

        return $out;
    }

    /**
     * Replace {{q-<questionId>}} with the response's answer and {{response_id}}
     * with the response id. Unknown tokens are left untouched.
     */
    protected function renderTemplate(string $text, FormResponse $response): string
    {
        if (! str_contains($text, '{{')) {
            return $text;
        }

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\-]+)\s*\}\}/', function (array $m) use ($response): string {
            $token = $m[1];

            if ($token === 'response_id') {
                return (string) $response->id;
            }

            if (str_starts_with($token, 'q-')) {
                $answer = $response->answers->firstWhere('question_id', (int) substr($token, 2));

                return $answer ? (string) $answer->value : '';
            }

            return $m[0];
        }, $text);
    }

    /**
     * End the workflow immediately with a chosen outcome (rejected | approved | terminated).
     */
    protected function runTerminate(FormResponse $response, FormWorkflowNode $node): void
    {
        $status = $node->config['status'] ?? 'terminated';

        if ($workflow = $response->workflow) {
            $workflow->status = $status;
            $workflow->current_node_id = null;
            $workflow->save();
        }

        $response->approval_status = $status;
        $response->save();

        $this->log($response, $node, null, 'terminated', $node->config['message'] ?? null);

        $this->notifySubmitter($response, $status);
    }

    public function evaluateCondition(FormResponse $response, FormWorkflowNode $node): bool
    {
        $config = $node->config ?? [];
        $rules = $this->conditionRules($config);

        if ($rules === []) {
            return true;
        }

        $answers = $response->answers()->pluck('value', 'question_id');
        $any = ($config['match'] ?? 'all') === 'any';

        foreach ($rules as $rule) {
            $values = $this->normalizeAnswer($answers[$rule['field']] ?? null);
            $matched = $this->compare($values, $rule['operator'], (string) $rule['value']);

            if ($any && $matched) {
                return true;
            }
            if (! $any && ! $matched) {
                return false;
            }
        }

        return ! $any;
    }

    /**
     * Normalize a condition's rules, supporting both the multi-rule shape and the
     * legacy single field/operator/value config.
     *
     * @param  array<string, mixed>  $config
     * @return array<int, array{field: mixed, operator: string, value: string}>
     */
    protected function conditionRules(array $config): array
    {
        $rules = isset($config['rules']) && is_array($config['rules'])
            ? $config['rules']
            : [['field' => $config['field'] ?? null, 'operator' => $config['operator'] ?? '=', 'value' => $config['value'] ?? '']];

        return collect($rules)
            ->filter(fn ($rule) => is_array($rule) && ! empty($rule['field']))
            ->map(fn (array $rule) => [
                'field' => $rule['field'],
                'operator' => $rule['operator'] ?? '=',
                'value' => (string) ($rule['value'] ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeAnswer($raw): array
    {
        if ($raw === null) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_map('strval', $decoded);
        }

        return [(string) $raw];
    }

    /**
     * @param  array<int, string>  $values
     */
    protected function compare(array $values, string $operator, string $expected): bool
    {
        $first = $values[0] ?? '';

        return match ($operator) {
            '=' => in_array($expected, $values, true) || $first === $expected,
            '!=' => ! (in_array($expected, $values, true) || $first === $expected),
            '>' => (float) $first > (float) $expected,
            '<' => (float) $first < (float) $expected,
            '>=' => (float) $first >= (float) $expected,
            '<=' => (float) $first <= (float) $expected,
            'contains' => collect($values)->contains(fn ($v) => str_contains(mb_strtolower($v), mb_strtolower($expected))),
            'not_contains' => ! collect($values)->contains(fn ($v) => str_contains(mb_strtolower($v), mb_strtolower($expected))),
            default => false,
        };
    }

    // ---- approval actions ---------------------------------------------------

    public function currentNode(FormResponse $response): ?FormWorkflowNode
    {
        $workflow = $response->workflow;

        if (! $workflow || $workflow->status !== 'pending' || ! $workflow->current_node_id) {
            return null;
        }

        return FormWorkflowNode::with('users')->find($workflow->current_node_id);
    }

    public function currentApprovers(FormResponse $response): Collection
    {
        $node = $this->currentNode($response);

        if (! $node) {
            return collect();
        }

        $approvedIds = $this->nodeApprovedUserIds($response, $node);

        return $node->users->reject(fn (User $u) => $approvedIds->contains($u->id))->values();
    }

    public function canAct(FormResponse $response, User $user): bool
    {
        $node = $this->currentNode($response);

        if (! $node || $node->type !== 'approval' || ! $node->users->contains('id', $user->id)) {
            return false;
        }

        return ! $this->nodeApprovedUserIds($response, $node)->contains($user->id);
    }

    public function approve(FormResponse $response, User $user, ?string $comment = null): void
    {
        $node = $this->currentNode($response);

        if (! $node || ! $this->canAct($response, $user)) {
            throw new RuntimeException('You are not allowed to approve this response.');
        }

        $this->log($response, $node, $user, 'approved', $comment);

        if (! $this->approvalComplete($response, $node)) {
            return;
        }

        $this->advanceFrom($response, $this->nextNode($node, 'next'));
    }

    public function reject(FormResponse $response, User $user, ?string $comment = null): void
    {
        $node = $this->currentNode($response);

        if (! $node || ! $this->canAct($response, $user)) {
            throw new RuntimeException('You are not allowed to reject this response.');
        }

        $this->log($response, $node, $user, 'rejected', $comment);

        $workflow = $response->workflow;
        $workflow->status = 'rejected';
        $workflow->save();

        $response->approval_status = 'rejected';
        $response->save();

        $this->notifySubmitter($response, 'rejected');
    }

    protected function approvalComplete(FormResponse $response, FormWorkflowNode $node): bool
    {
        if ($node->mode !== 'all') {
            return true;
        }

        $assigned = $node->users->pluck('id');
        $approved = $this->nodeApprovedUserIds($response, $node);

        return $assigned->diff($approved)->isEmpty();
    }

    protected function nodeApprovedUserIds(FormResponse $response, FormWorkflowNode $node): Collection
    {
        return FormResponseWorkflowAction::query()
            ->where('form_response_id', $response->id)
            ->where('node_id', $node->id)
            ->where('action', 'approved')
            ->pluck('user_id')
            ->unique()
            ->values();
    }

    // ---- notifications ------------------------------------------------------

    public function notifyCurrentApprovers(FormResponse $response): void
    {
        $this->currentApprovers($response)->each(
            fn (User $user) => $user->notify(new FormWorkflowNotification($response, 'pending'))
        );
    }

    public function notifySubmitter(FormResponse $response, string $kind): void
    {
        if (! $response->user_id) {
            return;
        }

        User::find($response->user_id)?->notify(new FormWorkflowNotification($response, $kind));
    }

    protected function log(FormResponse $response, ?FormWorkflowNode $node, ?User $user, string $action, ?string $comment = null): void
    {
        FormResponseWorkflowAction::create([
            'form_response_id' => $response->id,
            'node_id' => $node?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'comment' => $comment,
            'acted_at' => now(),
        ]);
    }
}
