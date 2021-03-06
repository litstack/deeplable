<?php

namespace Litstack\Deeplable;

use AwStudio\Deeplable\Facades\Translator;
use Ignite\Page\Actions\ActionModal;
use Ignite\Support\AttributeBag;
use Illuminate\Support\Collection;

class TranslateAction
{
    public function modal(ActionModal $modal)
    {
        $modal->title(__lit('deeplable.translate_deepl'))->confirmText(__lit('deeplable.translate'));
        $modal->form(function ($form) {
            $form
                ->select('locale')
                ->title(__lit('deeplable.language'))
                ->options(
                    collect(config('translatable.locales'))
                        ->filter(fn ($locale)      => $locale != config('translatable.fallback_locale'))
                        ->mapWithKeys(fn ($locale) => [$locale => $locale])
                        ->toArray()
                );

            $form
                ->boolean('force')
                ->title(__lit('deeplable.force'));
        });
    }

    public function run(Collection $models, AttributeBag $attributes)
    {
        $targetLanguage = $attributes->locale;
        $sourceLanguage = config('translatable.fallback_locale');

        if (! $targetLanguage) {
            return response()->danger(__lit('deeplable.messages.missing_locale'));
        }

        foreach ($models as $model) {
            Translator::for($model)->translate($model, $targetLanguage, $sourceLanguage, (bool) $attributes->force);

            $model->save();
        }

        return response()->success(__lit(
            'deeplable.messages.translated',
            [
                'target' => $targetLanguage,
                'source' => $sourceLanguage,
            ]
        ));
    }
}
