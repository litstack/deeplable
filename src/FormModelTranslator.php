<?php

namespace Litstack\Deeplable;

use Illuminate\Database\Eloquent\Model;
use AwStudio\Deeplable\Translators\BaseTranslator;

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
    protected function translateAttribute(Model $model, $attribute, $locale, $translation)
    {
        $model->update([$locale => [
            $attribute => $translation
        ]]);
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
        return array_keys($model->getTranslationsArray()[$locale] ?? []);
    }
}
