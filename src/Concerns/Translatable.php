<?php

namespace Mrgiant\FormBuilder\Concerns;

use App\Http\TranslatorAction\Translator;
use App\Models\Translation;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;

trait Translatable
{
    /**
     * Check if this model can translate.
     *
     * @return bool
     */
    public function translatable()
    {
        if (isset($this->translatable) && $this->translatable == false) {
            return false;
        }

        return ! empty($this->getTranslatableAttributes());
    }

    /**
     * Load translations relation.
     *
     * @return mixed
     */
    public function translations()
    {
        return $this->hasMany(Translation::class, 'foreign_key', $this->getKeyName())
            ->where('table_name', $this->getTable())
            // ->whereIn('locale', config('app.multilingual.locales', []));
            ->whereIn('locale', get_locals());
    }

    /**
     * This scope eager loads the translations for the default and the fallback locale only.
     * We can use this as a shortcut to improve performance in our application.
     *
     * @param  string|bool  $fallback
     */
    public function scopeWithTranslation(Builder $query, ?string $locale = null, $fallback = true)
    {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        if ($fallback === true) {
            $fallback = config('app.fallback_locale', 'en');
        }

        $query->with(['translations' => function (Relation $query) use ($locale, $fallback) {
            $query->where(function ($q) use ($locale, $fallback) {
                $q->where('locale', $locale);

                if ($fallback !== false) {
                    $q->orWhere('locale', $fallback);
                }
            });
        }]);
    }

    /**
     * This scope eager loads the translations for the default and the fallback locale only.
     * We can use this as a shortcut to improve performance in our application.
     *
     * @param  string|null|array  $locales
     * @param  string|bool  $fallback
     */
    public function scopeWithTranslations(Builder $query, $locales = null, $fallback = true)
    {
        if (is_null($locales)) {
            $locales = app()->getLocale();
        }

        if ($fallback === true) {
            $fallback = config('app.fallback_locale', 'en');
        }

        $query->with(['translations' => function (Relation $query) use ($locales, $fallback) {
            if (is_null($locales)) {
                return;
            }

            $query->where(function ($q) use ($locales, $fallback) {
                if (is_array($locales)) {
                    $q->whereIn('locale', $locales);
                } else {
                    $q->where('locale', $locales);
                }

                if ($fallback !== false) {
                    $q->orWhere('locale', $fallback);
                }
            });
        }]);
    }

    /**
     * Translate the whole model.
     *
     * @param bool[string $fallback
     */
    public function translate(?string $language = null, $fallback = true): Translator
    {
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        return (new Translator($this))->translate($language, $fallback);
    }

    /**
     * Get a single translated attribute.
     *
     * @param  null  $language
     * @return null
     */
    public function getTranslatedAttribute($attribute, $language = null, bool $fallback = true)
    {
        // If multilingual is not enabled don't check for translations
        if (! config('app.multilingual.enabled')) {
            return $this->getAttributeValue($attribute);
        }

        [$value] = $this->getTranslatedAttributeMeta($attribute, $language, $fallback);

        return $value;
    }

    public function getTranslationsOf($attribute, ?array $languages = null, $fallback = true)
    {
        if (is_null($languages)) {
            // $languages = config('app.multilingual.locales', [config('app.multilingual.default')]);
            $languages = get_locals() ?? [config('app.multilingual.default')];
        }

        $response = [];
        foreach ($languages as $language) {
            $response[$language] = $this->getTranslatedAttribute($attribute, $language, $fallback);
        }

        return $response;
    }

    public function getTranslatedAttributeMeta($attribute, $locale = null, $fallback = true)
    {
        // Attribute is translatable
        //
        if (! in_array($attribute, $this->getTranslatableAttributes())) {
            return [$this->getAttribute($attribute), config('app.multilingual.default'), false];
        }

        if (is_null($locale)) {
            $locale = app()->getLocale();
        }

        if ($fallback === true) {
            $fallback = config('app.fallback_locale', 'en');
        }

        $default = config('app.multilingual.default');

        if ($default == $locale) {
            return [$this->getAttribute($attribute), $default, true];
        }

        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $translations = $this->getRelation('translations')
            ->where('column_name', $attribute);

        $localeTranslation = $translations->where('locale', $locale)->first();

        if ($localeTranslation) {
            return [$localeTranslation->value, $locale, true];
        }

        if ($fallback == $locale) {
            return [$this->getAttribute($attribute), $locale, false];
        }

        if ($fallback == $default) {
            return [$this->getAttribute($attribute), $locale, false];
        }

        $fallbackTranslation = $translations->where('locale', $fallback)->first();

        if ($fallbackTranslation && $fallback !== false) {
            return [$fallbackTranslation->value, $locale, true];
        }

        return [null, $locale, false];
    }

    /**
     * Get attributes that can be translated.
     *
     * @return array
     */
    public function getTranslatableAttributes()
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    public function setAttributeTranslations($attribute, array $translations, $save = false)
    {
        $response = [];

        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $default = config('app.multilingual.default', 'en');
        // $locales = config('app.multilingual.locales', [$default]);
        $locales = get_locals() ?? [$default];

        foreach ($locales as $locale) {
            if (empty($translations[$locale])) {
                // continue;
                $translations[$locale] = '';
            }

            if ($locale == $default) {
                $this->$attribute = $translations[$locale];

                continue;
            }

            $tranlator = $this->translate($locale, false);
            $tranlator->$attribute = $translations[$locale];

            if ($save) {
                $tranlator->save();
            }

            $response[] = $tranlator;
        }

        return $response;
    }

    /**
     * Get entries filtered by translated value.
     *
     * @example  Class::whereTranslation('title', '=', 'zuhause', ['de', 'iu'])
     * @example  $query->whereTranslation('title', '=', 'zuhause', ['de', 'iu'])
     *
     * @param  string  $field  {required} the field your looking to find a value in.
     * @param  string  $operator  {required} value you are looking for or a relation modifier such as LIKE, =, etc.
     * @param  string  $value  {optional} value you are looking for. Only use if you supplied an operator.
     * @param  string|array  $locales  {optional} locale(s) you are looking for the field.
     * @param  bool  $default  {optional} if true checks for $value is in default database before checking translations.
     */
    public static function scopeWhereTranslation($query, string $field, string $operator, ?string $value = null, $locales = null, bool $default = true): Builder
    {
        if ($locales && ! is_array($locales)) {
            $locales = [$locales];
        }
        if (! isset($value)) {
            $value = $operator;
            $operator = '=';
        }

        $self = new static;
        $table = $self->getTable();

        return $query->whereIn(
            $self->getKeyName(),
            Translation::where('table_name', $table)
                ->where('column_name', $field)
                ->where('value', $operator, $value)
                ->when(! is_null($locales), function ($query) use ($locales) {
                    return $query->whereIn('locale', $locales);
                })
                ->pluck('foreign_key')
        )->when($default, function ($query) use ($field, $operator, $value) {
            return $query->orWhere($field, $operator, $value);
        });
    }

    public function hasTranslatorMethod($name)
    {
        if (! isset($this->translatorMethods)) {
            return false;
        }

        return isset($this->translatorMethods[$name]);
    }

    public function getTranslatorMethod($name)
    {
        if (! $this->hasTranslatorMethod($name)) {
            return;
        }

        return $this->translatorMethods[$name];
    }

    public function deleteAttributeTranslations(array $attributes, $locales = null)
    {
        $this->translations()
            ->whereIn('column_name', $attributes)
            ->when(! is_null($locales), function ($query) use ($locales) {
                $method = is_array($locales) ? 'whereIn' : 'where';

                return $query->$method('locale', $locales);
            })
            ->delete();
    }

    public function deleteAttributeTranslation($attribute, $locales = null)
    {
        $this->translations()
            ->where('column_name', $attribute)
            ->when(! is_null($locales), function ($query) use ($locales) {
                $method = is_array($locales) ? 'whereIn' : 'where';

                return $query->$method('locale', $locales);
            })
            ->delete();
    }

    /**
     * Prepare translations and set default locale field value.
     *
     * @return array translations
     */
    public function prepareTranslations(object $request)
    {
        $translations = [];

        // Translatable Fields
        $transFields = $this->getTranslatableAttributes();

        $fields = ! empty($request->attributes->get('breadRows')) ? array_intersect($request->attributes->get('breadRows'), $transFields) : $transFields;

        foreach ($fields as $field) {
            // Skip translatable fields that were not submitted (e.g. fields edited
            // through a different screen), instead of failing the whole request.
            if (! $request->input($field.'_i18n')) {
                Log::warning('Invalid Translatable field '.$field);

                continue;
            }

            $trans = json_decode($request->input($field.'_i18n'), true);

            // Set the default local value
            $request->merge([$field => $trans[config('app.multilingual.default', 'en')]]);

            $translations[$field] = $this->setAttributeTranslations(
                $field,
                $trans
            );

            // Remove field hidden input
            unset($request[$field.'_i18n']);
        }

        // Remove language selector input
        unset($request['i18n_selector']);

        return $translations;
    }

    /**
     * Prepare translations and set default locale field value.
     *
     * @return array translations
     */
    public function prepareTranslationsFromArray($field, object &$requestData)
    {
        $translations = [];

        $field = 'field_display_name_'.$field;

        if (empty($requestData[$field.'_i18n'])) {
            throw new Exception('Invalid Translatable field '.$field);
        }

        $trans = json_decode($requestData[$field.'_i18n'], true);

        // Set the default local value
        $requestData['display_name'] = $trans[config('app.multilingual.default', 'en')];

        $translations['display_name'] = $this->setAttributeTranslations(
            'display_name',
            $trans
        );

        // Remove field hidden input
        unset($requestData[$field.'_i18n']);

        return $translations;
    }

    /**
     * Save translations.
     *
     * @return void
     */
    public function saveTranslations($translations)
    {
        foreach ($translations as $field => $locales) {
            foreach ($locales as $locale => $translation) {
                $translation->save();
            }
        }
    }

    public function getAllTranslationFeildsAttribute()
    {

        $this->load('translations');

        $translations = [];

        foreach ($this->translatable as $key => $value) {
            $translations[$value.'_i18n']['en'] = $this->$value;

        }

        foreach ($this->translations as $key => $value) {

            $translations[$value->column_name.'_i18n'][$value->locale] = $value->value;

        }

        foreach ($translations as $key => &$value) {
            $value = json_encode($value);
        }

        return $translations;

    }

    public function getAllTranslationFeildsCurrentLangAttribute()
    {

        $this->load('translations');

        $translations = [];

        $lang = app()->getLocale();

        if ($lang == 'en') {
            foreach ($this->translatable as $key => $value) {
                $translations[$value.'_i18n'] = $this->$value;

            }

        } else {

            foreach ($this->translations as $key => $value) {

                if ($value->locale == $lang) {
                    $translations[$value->column_name.'_i18n'] = $value->value;
                }

            }

        }

        return $translations;

    }
}
