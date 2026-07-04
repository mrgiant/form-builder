<?php

namespace Mrgiant\FormBuilder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormResponseWorkflowAction extends Model
{
    protected $fillable = ['form_response_id', 'node_id', 'user_id', 'action', 'comment', 'acted_at'];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(FormWorkflowNode::class, 'node_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
