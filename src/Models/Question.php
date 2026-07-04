<?php

namespace Mrgiant\FormBuilder\Models;

use Mrgiant\FormBuilder\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use Translatable;

    protected $casts = [
        'options' => 'array',
    ];

    protected $translatable = ['label', 'description'];

    protected $appends = ['all_translation_feilds'];

    /**
     * Attributes that are mass assignable
     *
     * @var array Mass assignable fields
     */
    protected $fillable = ['form_id', 'question_type', 'label', 'description', 'unique', 'options', 'required', 'css_class', 'maximum_file_size', 'allow_only_specific_file_types', 'question_size_col', 'parent_id', 'label_free_text', 'css_styles', 'pattern_validation', 'pattern_validation_message'];

    /**
     * Get the survey that owns the question
     */
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order', 'ASC');
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (! empty($model->parent_id)) {
                $max = self::where('form_id', $model->form_id)->where('parent_id', $model->parent_id)->max('order');
            } else {
                $max = self::where('form_id', $model->form_id)->max('order');
            }

            $model->order = $max + 1;
        });

        static::saved(function ($model) {
            // cache()->clear();
        });

        static::deleted(function ($model) {
            // cache()->clear();
        });
    }
}
