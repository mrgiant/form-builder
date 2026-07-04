<?php

namespace Mrgiant\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormWorkflowNode extends Model
{
    protected $fillable = ['form_id', 'type', 'name', 'mode', 'config', 'pos_x', 'pos_y'];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Users tied to this node â€” approvers (approval node) or recipients (notification node).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'form_workflow_node_users', 'node_id', 'user_id');
    }

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(FormWorkflowEdge::class, 'from_node_id');
    }
}
