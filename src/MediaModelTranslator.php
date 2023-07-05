<?php

namespace Litstack\Deeplable;

use AwStudio\Deeplable\Translators\BaseTranslator;
use Illuminate\Database\Eloquent\Model;

class MediaModelTranslator extends BaseTranslator
{
    /**
     * Translate the custom properties attribute of the Media Model.
     *
     * @param  Model  $model
     * @param  string $attribute
     * @param  string $locale
     * @param  string $translation
     * @return void
     */
    protected function translateAttribute(Model $model, $attribute, $locale, $translation, bool $force = true)
    {
        $localeTranslations = $model->getCustomProperty($locale);

        if (!is_array($localeTranslations)) {
            $localeTranslations = [];
        }

        $model->setCustomProperty($locale, array_merge($localeTranslations, [$attribute => $translation]));
        // Quietly save the model to avoid Exceptions for missing files.
        $model->updated_at = now();
        $model->saveQuietly();
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
        return ($model->custom_properties);
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
        if (!array_key_exists($sourceLanguage, $attributes)) {
            return;
        }
        foreach ($attributes[$sourceLanguage] as $attribute => $value) {
            if (!$value) {
                continue;
            }

            if(
                array_key_exists($targetLang, $attributes) &&
                array_key_exists($attribute, $attributes[$targetLang]) &&
                !is_null($attributes[$targetLang][$attribute]) &&
                !$force
            ){
                continue;
            }

            $translation = $this->api->translate(
                (string) $value,
                $targetLang,
                $sourceLanguage
            );

            $this->translateAttribute($model, $attribute, $targetLang, $translation, $force);
        }
    }
}
