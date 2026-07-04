<?php

namespace Mrgiant\FormBuilder\Http\Controllers;

use Mrgiant\FormBuilder\Exports\FormExport;
use App\Http\Controllers\Controller;
use App\Http\Services\ReportsGenerator\PdfFooter;
use Mrgiant\FormBuilder\Models\Answer;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Excel;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ResponsesController extends Controller
{
    use Exportable;

    public function isJson($string)
    {
        if (is_numeric($string)) {
            return false;
        }
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getResponsesList(Form $form, Request $request)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $responses = FormResponse::where('form_id', $form->id)
            ->advancedFilter();

        $responses->getCollection()->transform(function ($r) {
            $r->created_at_f = $r->created_at ? $r->created_at->format('Y-m-d H:i') : '';

            return $r;
        });

        return response()->json($responses);
    }

    public function index(Form $form)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.responses.index', compact('form'));
    }

    public function ExportToExcel($form_id)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);

        $questions = [];
        $data = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
        }
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;
                // dd($res);
                if ($this->isJson($res)) {
                    $res = implode(',', json_decode($res));
                }

                $data[$r->id][$qid] = $res;
            }
            $data[$r->id]['date'] = $r->updated_at;
        }

        $orientation = 'landscape';
        $html = \View::make('admin.responses.general-pdf-template', compact('orientation', 'data', 'questions', 'form'));

        $file_name = 'simple.xlsx';

        return Excel::download(new FormExport($html), $file_name);
    }

    public function ExportToPDF($form_id)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);

        $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);

        $questions = [];
        $data = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
        }
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;
                // dd($res);
                if ($this->isJson($res)) {
                    $res = implode(',', json_decode($res));
                }

                $data[$r->id][$qid] = $res;
            }
            $data[$r->id]['date'] = $r->updated_at;
        }

        $orientation = 'landscape';
        $html = \View::make('admin.responses.general-pdf-template', compact('orientation', 'data', 'questions', 'form'))->render();

        return Pdf::html($html)
            ->format('a4')
            ->landscape()
            ->footerHtml(PdfFooter::defaultHtml());
    }

    public function RemoveAllResponses(Form $form)
    {
        abort_if(Gate::denies('delete_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $form->responses()->delete();

        return response()->json(['success' => true]);
    }

    public function destroy(Form $form, FormResponse $response)
    {
        abort_if(Gate::denies('delete_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $response->delete();

        return response()->json(['success' => true]);
    }

    public function RemoveSelectedResponses(Request $request, Form $form)
    {
        $this->validate($request, [
            'responses_selected' => 'required|array',
            'responses_selected.*' => 'exists:form_responses,id',
        ]);

        FormResponse::whereIn('id', request('responses_selected'))->delete();

        return back();
    }

    /**
     * Show the selected form
     *
     * @param  int  $id
     * @return Response
     */
    public function single_response($form_id, $id)
    {
        /*
        $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);
        $questions = [];
        $data = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
        }
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;
                // dd($res);
                if ($this->isJson($res)) {
                    $res = implode(',', json_decode($res));
                }

                $data[$r->id][$qid] = $res;
            }
            $data[$r->id]['date'] = $r->updated_at;
        }

        */

        // $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);
        $form = Form::with(['questions', 'responses', 'responses.answers'])
            ->whereHas('responses', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->find($form_id);
        $questions = [];
        $data = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
        }
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;

                if ($res) {
                    if ($this->isJson($res)) {
                        $data[$qid] = implode(',', json_decode($res));
                    } else {
                        $data[$qid] = $res;
                    }
                } else {
                    $data[$qid] = '';
                }
            }
        }

        return view('admin.responses.single', ['data' => $data, 'questions' => $questions, 'form' => $form, 'response_id' => $id]);
    }

    /**
     * Build a map of raw answer values keyed by the input name ("q-{id}"),
     * decoding JSON-array answers (checkboxes / multi-select) back to arrays
     * so a rendered form template can re-check / re-select the right values.
     *
     * @return array<string, mixed>
     */
    private function buildRawAnswers(Form $form): array
    {
        $answers = [];

        foreach ($form->responses as $response) {
            foreach ($response->answers as $answer) {
                $value = $answer->value;

                if ($this->isJson($value)) {
                    $value = json_decode($value, true);
                }

                $answers['q-'.$answer->question_id] = $value;
            }
        }

        return $answers;
    }

    /**
     * Build the label/answer maps for a single loaded response.
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function buildLabeledAnswers(Form $form): array
    {
        $questions = [];
        $data = [];

        foreach ($form->questions as $question) {
            $questions[$question->id] = $question->label;
        }

        foreach ($form->responses as $response) {
            foreach ($questions as $qid => $label) {
                $value = optional($response->answers->where('question_id', $qid)->first())->value;

                if ($value) {
                    $data[$qid] = $this->isJson($value) ? implode(',', json_decode($value)) : $value;
                } else {
                    $data[$qid] = '';
                }
            }
        }

        return [$data, $questions];
    }

    public function single_response_as_form($form_id, $id)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $form = Form::with(['questions', 'responses', 'responses.answers'])
            ->whereHas('responses', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->find($form_id);

        abort_if(! $form, Response::HTTP_NOT_FOUND);

        $submittedAt = optional($form->responses->first())->created_at;

        if (! empty($form->custom_html)) {
            return view('forms.custom_render_filled', [
                'form' => $form,
                'answers' => $this->buildRawAnswers($form),
                'submitted_at' => $submittedAt,
                'response_id' => $id,
            ]);
        }

        [$data, $questions] = $this->buildLabeledAnswers($form);

        return view('admin.responses.single_as_form', [
            'form' => $form,
            'data' => $data,
            'questions' => $questions,
            'submitted_at' => $submittedAt,
            'response_id' => $id,
        ]);
    }

    public function single_response_pdf($form_id, $id)
    {
        abort_if(Gate::denies('access_form'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $form = Form::with(['questions', 'responses', 'responses.answers'])
            ->whereHas('responses', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->find($form_id);

        abort_if(! $form, Response::HTTP_NOT_FOUND);

        $submittedAt = optional($form->responses->first())->created_at;
        $fileName = 'response-'.$id.'.pdf';

        if (! empty($form->custom_html)) {
            return Pdf::view('forms.custom_render_filled', [
                'form' => $form,
                'answers' => $this->buildRawAnswers($form),
                'submitted_at' => $submittedAt,
                'response_id' => $id,
            ])->format('a4')->download($fileName);
        }

        [$data, $questions] = $this->buildLabeledAnswers($form);

        return Pdf::view('forms.single_response_pdf', [
            'form' => $form,
            'data' => $data,
            'questions' => $questions,
            'submitted_at' => $submittedAt,
        ])->format('a4')->download($fileName);
    }

    public function single_response_without_auth($form_id, $id)
    {
        /*
        $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);
        $questions = [];
        $data = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
        }
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;
                // dd($res);
                if ($this->isJson($res)) {
                    $res = implode(',', json_decode($res));
                }

                $data[$r->id][$qid] = $res;
            }
            $data[$r->id]['date'] = $r->updated_at;
        }

        */

        // $form = Form::with(['questions', 'responses', 'responses.answers'])->find($form_id);
        $form = Form::with(['questions', 'responses', 'responses.answers'])
            ->whereHas('responses', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->find($form_id);
        $questions = [];
        $data = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
        }
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;

                if ($res) {
                    if ($this->isJson($res)) {
                        $data[$qid] = implode(',', json_decode($res));
                    } else {
                        $data[$qid] = $res;
                    }
                } else {
                    $data[$qid] = '';
                }
            }
        }

        return view('admin.responses.single', ['data' => $data, 'questions' => $questions, 'form' => $form, 'response_id' => $id]);
    }

    /**
     * Save a new form
     *
     * @param  Request  $request  Form request data
     * @return redirect to add question form
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'kiosk_mode' => 'boolean',
            'begin_at' => 'nullable|date',
            'end_at' => 'nullable|date',
        ]);

        if ($request->has('name')) {
            $slug = Str::slug($request->get('name'), '-');

            $request->request->set('slug', $slug);
        }

        $kiosk_mode = 0;

        if ($request->has('kiosk_mode')) {
            $kiosk_mode = 1;
        }

        $request->request->set('kiosk_mode', $kiosk_mode);

        // ok, we're valid, now to save form data as a new form:
        $form = Form::create(
            $request->all()
        );

        return redirect('admin/forms/questions/'.$form->id.'/create');
    }

    public function update(Request $request, Form $form)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'begin_at' => 'nullable|date',
            'end_at' => 'nullable|date',
        ]);

        $form->name = $request->input('name');
        $form->description = $request->input('description');
        $form->css = strip_tags($request->input('css'));
        $form->return_url = $request->input('return_url');
        $form->thank_you_message = $request->input('thank_you_message');
        if ($request->has('name')) {
            $slug = Str::slug($request->get('name'), '-');

            $request->request->set('slug', $slug);
        }

        $form->slug = $request->get('name');

        $kiosk_mode = 0;

        if ($request->has('kiosk_mode')) {
            $kiosk_mode = 1;
        }

        $form->kiosk_mode = $kiosk_mode;

        if ($request->input('begin_at') !== null) {
            $form->begin_at = date('Y-m-d H:i:s', strtotime($request->input('begin_at')));
        }
        if ($request->input('end_at') !== null) {
            $form->end_at = date('Y-m-d H:i:s', strtotime($request->input('end_at')));
        }
        $form->save();

        return redirect()->route('admin.forms.index');
    }

    public function csv($id)
    {
        // $form = Form::where('id', $id)->with(['questions', 'responses', 'responses.answers'])->first();
        $form = Form::with(['questions', 'responses', 'responses.answers'])->find($id);
        $questions = [];
        $data = [];

        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
            $data[1][$q->id] = $q->label;
        }

        $data[1]['Date'] = 'Date';

        // loop through the rest!
        foreach ($form->responses as $r) {
            foreach ($questions as $qid => $qlabel) {
                $res = @$r->answers->where('question_id', $qid)->first()->value;

                if ($this->isJson($res)) {
                    $res = implode(',', json_decode($res));
                }

                $data[$r->id][$qid] = $res;
            }
            $data[$r->id]['Date'] = $r->updated_at;
        }

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=form '.$form->id.'-'.date('Y-m-d').'.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);

        exit();
    }

    public function excel($id)
    {
        // $form = Form::where('id', $id)->with(['questions', 'responses', 'responses.answers'])->first();
        $form = Form::with(['questions', 'responses', 'responses.answers'])->find($id);

        $questions = [];
        $data = [];
        $data[1]['Status'] = 'Status';
        $multiple_choice = [];
        foreach ($form->questions as $q) {
            $questions[$q->id] = $q->label;
            // if(strpos($q->options, '|') !== false) {
            if ($q->question_type == 'Checkboxes') {
                $tmp_counter = 0;
                foreach ($q->options as $opt) {
                    $data[1][$q->id.':'.$tmp_counter] = $opt;
                    $multiple_choice[$q->id][$tmp_counter] = $opt;
                    $tmp_counter++;
                }
            } else {
                $data[1][$q->id] = $q->label;
            }
        }
        $data[1]['date'] = 'Date';
        // echo('"' . implode('","', $questions) . '","Date"' . "\n");
        foreach ($form->responses as $r) {
            $data[$r->id]['Status'] = ($r->processed == 1) ? 'Processed' : 'Not Processed';
            foreach ($questions as $qid => $qlabel) {
                $tmp_answer = @$r->answers()->where('question_id', $qid)->first()->value;
                if (! $tmp_answer) {
                    $tmp_answer = '';
                }
                if (isset($multiple_choice[$qid])) {
                    foreach ($multiple_choice[$qid] as $counter => $opt) {
                        if (stripos($tmp_answer, $opt) !== false) {
                            $data[$r->id][$qid.':'.$counter] = 'x';
                        } else {
                            $data[$r->id][$qid.':'.$counter] = '';
                        }
                    }
                } else {
                    $data[$r->id][$qid] = @$r->answers()->where('question_id', $qid)->first()->value;
                }
            }
            // echo('"' . implode('","', $data[$r->id]) . '","' . $r->updated_at . '"' . "\n");
            $data[$r->id]['date'] = $r->updated_at;
        }
        // die();
        $location = 0;
        $safe_colors = ['#0099cc', '#00ffcc', '#3399cc', '#9999cc', '#ccccff'];
        $merge_start = [];
        $merge_end = [];
        $cell_colors = [];
        if (count($multiple_choice) > 0) {
            foreach ($data[1] as $k => $value) {
                if (strpos($k, ':')) { // must be a multiple choice response:
                    // grab the question ID:
                    $tmp_id = substr($k, 0, strpos($k, ':'));
                    $data[0][$k] = $questions[$tmp_id];
                    if (! isset($merge_start[$tmp_id])) {
                        $merge_start[$tmp_id] = $location;
                        if (count($safe_colors) > 0) {
                            $cell_colors[$tmp_id] = array_pop($safe_colors);
                        } else {
                            $cell_colors[$tmp_id] = '#eeeeee';
                        }
                    }
                    $merge_end[$tmp_id] = $location;
                } else {
                    $data[0][$k] = '';
                }
                $location++;
            }
        }
        ksort($data);

        Excel::download('Survey '.$form->id.' Results', function ($excel) use ($data, $form, $multiple_choice, $merge_start, $merge_end, $cell_colors) {
            $excel->setTitle('Survey '.$form->id.' Results');
            $excel->sheet('Results', function ($sheet) use ($data, $multiple_choice, $merge_start, $merge_end, $cell_colors) {
                $sheet->fromArray($data, null, 'A1', false, false);
                $last_letter = chr(65 + count($data[1]));
                $sheet->getStyle('A1:'.$last_letter.'1')->getFont()->setBold(true);
                if (count($multiple_choice) > 0) {
                    foreach ($merge_start as $tmp_id => $location) {
                        $start = chr(65 + $location).'1';
                        $end = chr(65 + $merge_end[$tmp_id]).'1';
                        $sheet->mergeCells($start.':'.$end);
                        $sheet->cell($start, function ($cell) use ($cell_colors, $tmp_id) {
                            $cell->setAlignment('center');
                            $cell->setBackground($cell_colors[$tmp_id]);
                        });
                        $sheet->cells(chr(65 + $location).'2:'.chr(65 + $merge_end[$tmp_id]).'2', function ($cells) use ($cell_colors, $tmp_id) {
                            $cells->setBackground($cell_colors[$tmp_id]);
                        });
                    }
                    $sheet->getStyle('A2:'.$last_letter.'2')->getFont()->setBold(true);
                }
            });
        });
    }

    public function ViewCharts($id)
    {
        $form = Form::where('id', $id)->with(['questions'])->first();

        $questions = [];
        $data = [];

        foreach ($form->questions as $q) {
            if (
                $q->question_type == 'Drop-down' ||
                $q->question_type == 'Checkboxes' ||
                $q->question_type == 'Multiple choice'
            ) {
                $answers_options = [];

                $answers = Answer::where('question_id', $q->id)->get();

                foreach ($answers as $ans) {
                    if ($this->isJson($ans->value)) {
                        foreach (json_decode($ans->value) as $option) {
                            $answers_options[] = $option;
                        }
                    } else {
                        $answers_options[] = $ans->value;
                    }
                }

                $value_count = array_count_values($answers_options);

                $question = [

                    'question_label' => $q->order.'. '.$q->label,
                    'labels' => array_keys($value_count),
                    'data' => array_values($value_count),

                ];

                $questions[] = $question;
            }
        }

        return view('admin.forms.ViewCharts', compact('questions'));
    }
}
