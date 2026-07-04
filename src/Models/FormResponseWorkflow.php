<?php

namespace Mrgiant\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormResponseWorkflow extends Model
{
    public $table = 'gl_form_response_workflows';

    protected $fillable = ['form_response_id', 'status', 'current_node_id'];

    public function formResponse(): BelongsTo
    {
        return $this->belongsTo(FormResponse::class);
    }

    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(FormWorkflowNode::class, 'current_node_id');
    }
}
