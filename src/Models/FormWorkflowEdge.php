<?php

namespace Mrgiant\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormWorkflowEdge extends Model
{
    protected $fillable = ['form_id', 'from_node_id', 'to_node_id', 'branch'];

    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(FormWorkflowNode::class, 'from_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(FormWorkflowNode::class, 'to_node_id');
    }
}
