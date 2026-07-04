<?php

namespace Mrgiant\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    /**
     * Attributes that are mass assignable
     *
     * @var array Mass assignable fields
     */
    protected $fillable = ['form_response_id', 'question_id', 'value'];

    /**
     * Get the Form Response associated with this answer
     */
    public function FormResponse()
    {
        return $this->belongsTo(FormResponse::class);
    }

    /**
     * Get the question associated with this answer
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
