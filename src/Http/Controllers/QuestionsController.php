<?php

namespace Mrgiant\FormBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\Question;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class QuestionsController extends Controller
{
    public function index(Form $form, Request $request)
    {
        // abort_if(Gate::denies('access_question_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $questions = Question::where('form_id', $form->id)->whereNull('parent_id')->with('children')->orderBy('order', 'ASC')

                ->get();

            return response($questions, 200);
        }

        return view('admin.forms.addquestions', compact('form'));
    }

    public function get_questions(Form $form, Request $request)
    {
        // abort_if(Gate::denies('access_question_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $questions = Question::where('form_id', $form->id)->orderBy('order', 'ASC')
            ->get();

        return response($questions, 200);
    }

    public function create(Form $form)
    {
        abort(404);
    }

    public function store(Request $request, Form $form)
    {
        // abort_if(Gate::denies('create_question_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->validate($request, [
            'label' => 'required|max:255',
            'question_type' => 'required',

            'options' => 'required_if:type,select,checkbox-list,section',

        ]);

        if (! empty($request->options)) {
            $request->request->set('options', json_encode($request->options));
        }

        $request->request->set('description', ! empty($request->description) ? $request->description : '');

        if ($request->parent_id == 'null' || $request->parent_id == 'undefined') {
            $request->request->set('parent_id', null);
        }

        $request->request->set('pattern_validation', ! empty($request->pattern_validation) ? $request->pattern_validation : '');

        $request->request->set('pattern_validation_message', ! empty($request->pattern_validation_message) ? $request->pattern_validation_message : '');

        $question = new Question;
        $question->form_id = $form->id;

        $translations = is_bread_translatable($question)
            ? $question->prepareTranslations($request)
            : [];

        $question->fill($request->only('form_id', 'question_type', 'label', 'unique', 'description', 'options', 'required', 'css_class', 'maximum_file_size', 'allow_only_specific_file_types', 'question_size_col', 'parent_id', 'label_free_text', 'css_styles', 'pattern_validation', 'pattern_validation_message'));
        $question->save();

        if (count($translations) > 0) {
            $question->saveTranslations($translations);
        }

        return response()->json(['success' => 'Data Added successfully.']);
    }

    public function import(Request $request, Form $form): JsonResponse
    {
        $validated = $this->validate($request, [
            'questions' => 'required|array|min:1',
            'questions.*.label' => 'required|string|max:255',
            'questions.*.question_type' => 'required|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'nullable|string',
            'questions.*.question_size_col' => 'nullable|string',
            'questions.*.required' => 'nullable|boolean',
            'questions.*.css_styles' => 'nullable|string',
        ]);

        Question::where('form_id', $form->id)->delete();

        $questionIds = [];

        foreach ($validated['questions'] as $item) {
            $question = new Question;
            $question->form_id = $form->id;
            $question->question_type = $item['question_type'];
            $question->label = $item['label'];
            $question->description = '';
            $question->options = ! empty($item['options']) ? array_values($item['options']) : null;
            $question->required = ! empty($item['required']) ? 1 : 0;
            $question->unique = 0;
            $question->question_size_col = $item['question_size_col'] ?? 'col-span-12';
            $question->css_styles = $item['css_styles'] ?? '';
            $question->pattern_validation = '';
            $question->pattern_validation_message = '';
            $question->save();
            $questionIds[] = $question->id;
        }

        return response()->json([
            'success' => 'Questions imported successfully.',
            'created' => count($questionIds),
            'question_ids' => $questionIds,
        ]);
    }

    public function edit($id, Question $question)
    {
        abort_if(Gate::denies('edit_question_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (request()->ajax()) {
            return response()->json(['data' => $question]);
        }
    }

    public function update(Request $request, Form $form, Question $question)
    {
        abort_if(Gate::denies('edit_question_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $rules = [
            'label' => 'required|max:255',
            'question_type' => 'required',
            'options' => 'required_if:type,select,checkbox-list,section',
        ];

        $error = Validator::make($request->all(), $rules);

        if ($error->fails()) {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        if (! empty($request->options)) {
            $request->request->set('options', json_encode($request->options));
        }

        $request->request->set('description', ! empty($request->description) ? $request->description : '');

        if ($request->parent_id == 'null' || $request->parent_id == 'undefined') {
            $request->request->set('parent_id', null);
        }

        $request->request->set('pattern_validation', ! empty($request->pattern_validation) ? $request->pattern_validation : '');

        $request->request->set('pattern_validation_message', ! empty($request->pattern_validation_message) ? $request->pattern_validation_message : '');

        $translations = is_bread_translatable($question)
            ? $question->prepareTranslations($request)
            : [];

        $question->update($request->only('form_id', 'question_type', 'label', 'unique', 'description', 'options', 'required', 'css_class', 'maximum_file_size', 'allow_only_specific_file_types', 'question_size_col', 'parent_id', 'label_free_text', 'css_styles', 'pattern_validation', 'pattern_validation_message'));

        if (count($translations) > 0) {
            $question->saveTranslations($translations);
        }

        return response()->json(['success' => 'Data Updated successfully.']);
    }

    public function destroy($form_id, Question $question, Request $request)
    {
        abort_if(Gate::denies('delete_question_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (is_bread_translatable($question)) {
            $question->deleteAttributeTranslations($question->getTranslatableAttributes());
        }

        $question->delete();

        return '';
    }

    public function QuestionsUpdateOrder(Request $request)
    {
        // abort_if(Gate::denies('edit_order_category_Diagnosis_And_Treatment'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        foreach ($request->Questions as $index => $Question) {
            Question::where('id', $Question['id'])->update([
                'order' => $index + 1,
                'parent_id' => $request->parent_id,
            ]);
        }

        return response('updated', 200);
    }
}
