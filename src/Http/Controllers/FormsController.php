<?php

namespace Mrgiant\FormBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Mrgiant\FormBuilder\Models\Form;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FormsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.forms.index');
    }

    public function getFormsList(Request $request)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $forms = Form::withCount('responses')->advancedFilter();

        $forms->getCollection()->transform(function ($item) {
            $item->responses_count_f = $item->responses_count ?? 0;
            $item->begin_at_f = $item->begin_at ? Carbon::parse($item->begin_at)->format('d-m-Y H:i') : '';
            $item->end_at_f = $item->end_at ? Carbon::parse($item->end_at)->format('d-m-Y H:i') : '';
            $item->manage_url = url('admin/forms/'.$item->id.'/questions');

            return $item;
        });

        return response()->json($forms);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('create_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validator = validator($request->all(), [
            'name' => 'required|max:255',
            'begin_at' => 'nullable|date',
            'end_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $form = new Form;

        $translations = is_bread_translatable($form)
            ? $form->prepareTranslations($request)
            : [];

        $form->name = $request->name;
        $form->description = $request->description;
        $form->thank_you_message = $request->thank_you_message;
        $form->close_message = $request->close_message;
        $form->not_start_message = $request->not_start_message;
        $form->begin_at = $request->begin_at ? Carbon::parse($request->begin_at) : null;
        $form->end_at = $request->end_at ? Carbon::parse($request->end_at) : null;
        $form->status = $request->status ? 1 : 0;
        $form->slug = Str::uuid()->toString();
        $form->save();

        if (count($translations) > 0) {
            $form->saveTranslations($translations);
        }

        return response()->json(['success' => true, 'id' => $form->id]);
    }

    public function update(Request $request, Form $form)
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validator = validator($request->all(), [
            'name' => 'required|max:255',
            'begin_at' => 'nullable|date',
            'end_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $translations = is_bread_translatable($form)
            ? $form->prepareTranslations($request)
            : [];

        $form->name = $request->name;
        $form->description = $request->description;
        $form->thank_you_message = $request->thank_you_message;
        $form->close_message = $request->close_message;
        $form->not_start_message = $request->not_start_message;
        $form->begin_at = $request->begin_at ? Carbon::parse($request->begin_at) : null;
        $form->end_at = $request->end_at ? Carbon::parse($request->end_at) : null;
        $form->status = $request->status ? 1 : 0;
        $form->save();

        if (count($translations) > 0) {
            $form->saveTranslations($translations);
        }

        return response()->json(['success' => true]);
    }

    public function design(Form $form): JsonResponse
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json([
            'custom_html' => $form->custom_html,
            'custom_head' => $form->custom_head,
            'custom_js' => $form->custom_js,
            'has_custom' => ! empty($form->custom_html),
            'all_translation_feilds' => $form->all_translation_feilds,
        ]);
    }

    public function saveDesign(Request $request, Form $form): JsonResponse
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->validate($request, [
            'custom_html' => 'required|string',
            'custom_head' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'custom_html_i18n' => 'nullable|string',
            'custom_head_i18n' => 'nullable|string',
            'custom_js_i18n' => 'nullable|string',
        ]);

        $default = config('app.multilingual.default', 'en');
        $fields = ['custom_html', 'custom_head', 'custom_js'];
        $translationsToSave = [];

        foreach ($fields as $field) {
            $i18nRaw = $request->input($field.'_i18n');
            $trans = $i18nRaw ? json_decode($i18nRaw, true) : null;

            if (is_array($trans)) {
                $form->$field = $trans[$default] ?? ($request->input($field) ?? '');
                $translationsToSave[$field] = $trans;
            } else {
                $form->$field = $request->input($field) ?? '';
            }
        }

        $form->save();

        foreach ($translationsToSave as $field => $trans) {
            $form->setAttributeTranslations($field, $trans, true);
        }

        return response()->json(['success' => true]);
    }

    public function deleteDesign(Form $form): JsonResponse
    {
        abort_if(Gate::denies('edit_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $form->custom_html = null;
        $form->custom_head = null;
        $form->custom_js = null;
        $form->save();

        if (is_bread_translatable($form)) {
            $form->deleteAttributeTranslations(['custom_html', 'custom_head', 'custom_js']);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Form $form)
    {
        abort_if(Gate::denies('delete_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (is_bread_translatable($form)) {
            $form->deleteAttributeTranslations($form->getTranslatableAttributes());
        }

        Storage::disk('form_storage')->deleteDirectory('Form_'.$form->id);
        $form->delete();

        return response()->json(['success' => true]);
    }

    public function downloadAttachment($attachment)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return Storage::disk('form_storage')->download(str_replace('slash_mw_attachment', '/', $attachment));
    }

    public function showPublic(Form $form)
    {
        $now = now();
        $beginAt = $form->begin_at ? Carbon::parse($form->begin_at) : null;
        $endAt = $form->end_at ? Carbon::parse($form->end_at) : null;

        return response()->json([
            'id' => $form->id,
            'name' => $form->name,
            'description' => $form->description,
            'thank_you_message' => $form->thank_you_message,
            'close_message' => $form->close_message,
            'not_start_message' => $form->not_start_message,
            'status' => (int) $form->status,
            'begin_at' => $form->begin_at,
            'end_at' => $form->end_at,
            'all_translation_feilds' => $form->all_translation_feilds,
            'is_closed' => $form->status == 0 || ($endAt && $endAt->isPast()),
            'is_not_started' => $beginAt && $beginAt->isFuture(),
        ]);
    }

    // Keep blade show for email link compatibility
}
