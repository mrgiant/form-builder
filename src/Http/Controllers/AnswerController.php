<?php

namespace Mrgiant\FormBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Mrgiant\FormBuilder\Services\WorkflowRunner;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Rules\UniqueAnswerResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnswerController extends Controller
{
    public function response(Form $form)
    {
        if (! empty($form->custom_html)) {
            return view('forms.custom_render', ['form' => $form]);
        }

        return view('admin.forms.Answer', ['form' => $form]);
    }

    public function GetMIMETypes($file_type)
    {
        $mime_type_result = '';

        /*

'                   Image',
                    'Video',
                    'Audio',
                    'Pdf',
                    'Word',
                    'Powerpoint',
                    'Excel',
                    'Text',
                    'Zip',

        */

        if ($file_type == 'Image') {
            $mime_type_result = 'image/jpeg,image/jpg,image/gif,image/png,image/bmp';
        }

        if ($file_type == 'Video') {
            $mime_type_result = 'video/x-flv,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/ogg,video/webm,video/3gpp';
        }

        if ($file_type == 'Audio') {
            $mime_type_result = 'audio/aac,audio/mp4,audio/mpeg,audio/ogg,audio/wav,audio/webm,audio/mp3,audio/3gpp';
        }

        if ($file_type == 'Pdf') {
            $mime_type_result = 'application/pdf';
        }

        if ($file_type == 'Word') {
            $mime_type_result = 'application/vnd.ms-word,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }

        if ($file_type == 'Powerpoint') {
            $mime_type_result = 'application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation';
        }

        if ($file_type == 'Excel') {
            $mime_type_result = 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        if ($file_type == 'Text') {
            $mime_type_result = 'text/css,text/html,text/plain,text/tab';
        }

        if ($file_type == 'Zip') {
            $mime_type_result = 'application/x-gzip,application/x-bzip2,application/x-tar,application/x-tar,application/zip,application/x-compressed-zip';
        }

        return $mime_type_result;
    }

    public function store(Request $request, Form $form)
    {
        /*
        if ($form->id != $request->input('form_id')) {
            if (
                $request->has('return_url')
                && strlen($request->input('return_url')) > 5
            ) {

                return redirect()->away($request->input('return_url'));
            }
        }

        */

        $answerArray = [];
        $validationArray = [];
        $messageArray = [];

        // loop through questions and check for answers
        foreach ($form->questions as $question) {
            if ($question->required) {
                // 'required|unique:posts|max:255'

                $validationArray['q-'.$question->id][] = 'required';
                // $validationArray['q-' . $question->id] =  'required';
                $messageArray['q-'.$question->id.'.required'] = $question->label.' is required';
            }

            if ($question->question_type == 'Email') {
                $validationArray['q-'.$question->id][] = 'email';
                $messageArray['q-'.$question->id.'.email'] = $question->label.' is not a valid email address';
            }

            if ($question->question_type == 'Number') {
                $validationArray['q-'.$question->id][] = 'numeric';
                $messageArray['q-'.$question->id.'.numeric'] = $question->label.' is not a valid number';
            }

            if ($question->unique) {
                // $validationArray['q-' . $question->id] =  new UniqueAnswerResponses($form->id,$question->id);

                $validationArray['q-'.$question->id][] = new UniqueAnswerResponses($form->id, $question->id, $question->label);

                $messageArray['q-'.$question->id.'.UniqueAnswerResponses'] = $question->label.' is not a unique';
            }

            if ($question->question_type == 'File upload') {
                $file_type = $this->GetMIMETypes($question->allow_only_specific_file_types);
                $file_size = ! empty($question->maximum_file_size) ? (str_replace('MB', '', $question->maximum_file_size) * 1000) : 5 * 1000;
                // $validationArray['q-' . $question->id] =  'mimetypes:'.$file_type;

                $validationArray['q-'.$question->id][] = 'mimetypes:'.$file_type;
                $messageArray['q-'.$question->id.'.mimetypes'] = $question->label.'must be a file of type: '.$file_type;

                // $validationArray['q-' . $question->id] =  'max:'.$file_size;
                $validationArray['q-'.$question->id][] = 'max:'.$file_size;
                $messageArray['q-'.$question->id.'.max'] = $question->label.'may not be greater than '.$question->maximum_file_size;
            }

            if ($request->has('q-'.$question->id)) {
                if ($question->question_type == 'File upload') {
                    // dd($request->file('q-' . $question->id));
                } elseif (is_array($request->input('q-'.$question->id)) && count($request->input('q-'.$question->id))) {
                    $answerArray[$question->id] = [
                        'value' => json_encode($request->input('q-'.$question->id)),
                        'question_id' => $question->id,
                    ];
                } elseif (strlen(trim($request->input('q-'.$question->id))) > 0) {
                    $answerArray[$question->id] = [
                        'value' => $request->input('q-'.$question->id),
                        'question_id' => $question->id,
                    ];
                } // I guess there is an empty string
            }
        }

        $this->validate($request, $validationArray, $messageArray);

        foreach ($form->questions as $question) {
            if ($request->has('q-'.$question->id)) {
                if ($question->question_type == 'File upload') {
                    if ($request->hasFile('q-'.$question->id)) {
                        $daynmic_file_path = Storage::disk('form_storage')->putFileAs('Form_'.$form->id, $request->file('q-'.$question->id), uniqid().'_'.trim($request->file('q-'.$question->id)->getClientOriginalName()));

                        $answerArray[$question->id] = [
                            'value' => route('admin.forms.downloadAttachment', str_replace('/', 'slash_mw_attachment', $daynmic_file_path)),
                            'question_id' => $question->id,
                        ];
                    }
                }
            }
        }

        // if no errors, submit form!
        if (count($answerArray) > 0) {
            $sr = new FormResponse(['form_id' => $form->id]);
            $sr->save();
            foreach ($answerArray as $qid => $ans) {
                // print_r($ans);
                $sr->answers()->create($ans);
            }

            app(WorkflowRunner::class)->start($sr);
        }

        return response()->json(['success' => 'Data added successfully.']);

        /*
        if (
            $form->return_url
            && strlen($form->return_url) > 5
            && !$form->kiosk_mode
        ) {
            return redirect()->away($form->return_url);
        } else {
            return redirect('thanks/' . $form->id);
        }
        */
    }
}
