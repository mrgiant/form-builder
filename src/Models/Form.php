<?php

namespace Mrgiant\FormBuilder\Models;

use Mrgiant\FormBuilder\Support\HasAdvancedFilter;
use Mrgiant\FormBuilder\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasAdvancedFilter;
    use Translatable;

    public $orderable = ['id', 'name', 'slug', 'status', 'created_at'];

    public $filterable = ['id', 'name', 'slug', 'status', 'created_at'];

    /**
     * Attributes that are mass assignable
     *
     * @var array Mass assignable fields
     */
    protected $fillable = [
        'name', 'description',
        'thank_you_message', 'close_message', 'not_start_message',
        'begin_at', 'end_at',
        'custom_html', 'custom_head', 'custom_js',
    ];

    public $table = 'gl_forms';

    protected $translatable = ['name', 'description', 'thank_you_message', 'close_message', 'not_start_message', 'custom_html', 'custom_head', 'custom_js'];

    protected $appends = ['all_translation_feilds'];

    protected $casts = [
        'emails' => 'array',
    ];

    // protected $date = ['begin_at', 'end_at', 'created_at', 'updated_at'];

    public function getActiveAttribute()
    {
        return true;
        if (($this->begin_at == null || strtotime($this->begin_at) < time())
            && $this->end_at == null || strtotime($this->end_at) > time()
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the questions associated with this form
     */
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order')->where('question_type', '!=', 'Group')->where('question_type', '!=', 'Label');
    }

    public static function named($slug)
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Get the responses associated with this form
     */
    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }

    /**
     * Workflow nodes (approval / notification / condition) for this form.
     */
    public function workflowNodes()
    {
        return $this->hasMany(FormWorkflowNode::class);
    }

    /**
     * Directed edges connecting this form's workflow nodes.
     */
    public function workflowEdges()
    {
        return $this->hasMany(FormWorkflowEdge::class);
    }

    public function hasWorkflow(): bool
    {
        return $this->workflowNodes()->exists();
    }
}
