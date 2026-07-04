<?php

namespace Mrgiant\FormBuilder\Models;

use Mrgiant\FormBuilder\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    use HasAdvancedFilter;

    public $orderable = ['id', 'ip', 'created_at'];

    public $filterable = ['id', 'ip', 'created_at'];

    /**
     * Attributes that are mass assignable
     *
     * @var array Mass assignable fields
     */
    protected $fillable = ['form_id', 'ip', 'approval_status'];

    /**
     * Casting column types
     *
     * @var array columns that need their types cast
     */
    protected $casts = [
        'processed' => 'boolean',
    ];

    /**
     * Get the survey that owns the response
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the answers that belong to this response
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * The workflow run (tracker head) for this response.
     */
    public function workflow()
    {
        return $this->hasOne(FormResponseWorkflow::class);
    }

    /**
     * The workflow action/event timeline for this response.
     */
    public function workflowActions()
    {
        return $this->hasMany(FormResponseWorkflowAction::class)->orderBy('acted_at');
    }

    public function markAsProcessed()
    {
        $this->processed = 1;

        return $this->save();
    }
}
