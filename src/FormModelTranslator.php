<?php

namespace Litstack\Deeplable;

use Closure;
use Ignite\Crud\Models\Media;
use Ignite\Crud\Models\Repeatable;
use Illuminate\Database\Eloquent\Model;
use AwStudio\Deeplable\Facades\Translator;
use Illuminate\Database\Eloquent\Collection;
use AwStudio\Deeplable\Translators\BaseTranslator;
use Litstack\Meta\Models\Meta;

class FormModelTranslator extends BaseTranslator
{
    /**
     * Translate the given model attribute.
     *
     * @param  Model  $model
     * @param  string $attribute
     * @param  string $locale
     * @param  string $translation
     * @return void
     */
    protected function translateAttribute(Model $model, $attribute, $locale, $translation, bool $force = true)
    {
        $value = $this->withLocale(
            $locale,
            fn () => $model->getAttribute($attribute)
        );

        if (! $force && $value && ! is_object($value)) {
            return;
        }

        if (! is_object($value)) {
            $model->update([$locale => [
                $attribute => $translation,
            ]]);
        }
    }

    /**
     * Temporary set locale.
     *
     * @param  string  $locale
     * @param  Closure $clousre
     * @return void
     */
    protected function withLocale($locale, Closure $closure)
    {
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);

        $value = $closure();

        app()->setLocale($originalLocale);

        return $value;
    }

    /**
     * Get a list of the translated attributes of a model.
     *
     * @param  Model  $model
     * @param  string $locale
     * @return array
     */
    public function getTranslatedAttributes(Model $model, $locale)
    {
        return $model->fields->map(fn ($field) => $field->id)->toArray();
    }

    /**
     * Translate all translatable attributes of a model.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param mixed $attributes
     * @param string $targetLang
     * @param string|null $sourceLanguage
     * @param bool $force
     * @return void
     */
    public function translateAttributes(Model $model, $attributes, string $targetLang, string | null $sourceLanguage = null, bool $force = true)
    {
        foreach ($attributes as $attribute) {
            if (($collection = $model->getAttribute($attribute)) instanceof Collection) {
                $this->translateRepeatablesInCollection($collection, $targetLang, $force);
                continue;
            }

            $translation = $this->api->translate(
                (string) $model->getAttribute($attribute),
                $targetLang,
                $sourceLanguage
            );

            $this->translateAttribute($model, $attribute, $targetLang, $translation, $force);
        }

        $this->translateMeta($model, $targetLang, $force);
    }

    
    /**
     * Translate all Reapeatables in the collection.
     *
     * @param Collection $collection
     * @param string $locale
     * @param boolean $force
     * @return void
     */
    public function translateRepeatablesInCollection(Collection $collection, string $locale, bool $force = true)
    {
        foreach ($collection as $item) {
            if ($item instanceof Repeatable) {
                Translator::for($item)
                    ->translate($item, $locale, config('translatable.fallback_locale'), $force);
            }
            if($item instanceof Media){
                Translator::for($item)
                    ->translate($item, $locale, config('translatable.fallback_locale'), $force);
            }
        }
    }

    /**
     * Translate meta.
     *
     * @param Model $model
     * @param string $targetLang
     * @param boolean $force
     * @return void
     */
    public function translateMeta(Model $model, string $targetLang, bool $force = true)
    {
        // check if model has meta
        if (method_exists($model, 'meta')) {
            $item = $model->meta()->first();

            if ($item instanceof Meta) {
                Translator::for($item)
                    ->translate($item, $targetLang, config('translatable.fallback_locale'), $force);
            }
        }
    }
}
