<?php

namespace Mrgiant\FormBuilder\Rules;

use Mrgiant\FormBuilder\Models\Answer;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueAnswerResponses implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $question_id;

    protected $form_id;

    protected $label;

    public function __construct($form_id, $question_id, $label)
    {
        $this->question_id = $question_id;
        $this->form_id = $form_id;
        $this->label = $label;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $answer_count = Answer::where('gl_answers.value', 'like', $value)
          ->select(DB::raw('count(*) as total_count'))
             ->join('gl_form_responses', 'gl_answers.form_response_id', '=', 'gl_form_responses.id')
             ->where('gl_answers.question_id', $this->question_id)
             ->where('gl_form_responses.form_id', $this->form_id)->first();

        return $answer_count->total_count <= 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The '.$this->label.' has already been taken.';
    }
}
