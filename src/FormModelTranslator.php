<?php

namespace Litstack\Deeplable;

use AwStudio\Deeplable\Facades\Translator;
use AwStudio\Deeplable\Translators\BaseTranslator;
use Closure;
use Ignite\Crud\Models\LitFormModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
        } elseif ($value instanceof Collection) {
            foreach ($value as $child) {
                if ($child instanceof LitFormModel) {
                    Translator::for($child)
                        ->translate($child, $locale, config('translatable.fallback_locale'), $force);
                }
            }
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
}
