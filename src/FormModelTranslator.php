<?php

namespace Litstack\Deeplable;

use AwStudio\Deeplable\Facades\Translator;
use Illuminate\Database\Eloquent\Model;
use AwStudio\Deeplable\Translators\BaseTranslator;
use Ignite\Crud\Models\LitFormModel;
use Illuminate\Database\Eloquent\Collection;

class FormModelTranslator extends BaseTranslator
{
    /**
     * Translate the given model attribute.
     *
     * @param Model $model
     * @param string $attribute
     * @param string $locale
     * @param string $translation
     * @return void
     */
    protected function translateAttribute(Model $model, $attribute, $locale, $translation, bool $force = true)
    {
        $value = $model->getAttribute($attribute);
        
        if (! $force && $value && ! is_object($value)) {
            return;
        }

        if (! is_object($value)) {
            $model->update([$locale => [
                $attribute => $translation
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
     * Get a list of the translated attributes of a model.
     *
     * @param Model $model
     * @param string $locale
     * @return array
     */
    public function getTranslatedAttributes(Model $model, $locale)
    {
        return $model->fields->map(fn ($field) => $field->id)->toArray();
    }
}
