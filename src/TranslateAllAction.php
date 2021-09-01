<?php

namespace Litstack\Deeplable;

use Ignite\Support\AttributeBag;
use Illuminate\Support\Collection;
use Ignite\Page\Actions\ActionModal;
use Ignite\Support\Vue\ButtonComponent;
use Illuminate\Support\Facades\Artisan;
use AwStudio\Deeplable\Facades\Translator;

class TranslateAllAction
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
                        ->filter(fn ($locale) => $locale != config('translatable.fallback_locale'))
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

        Artisan::call('deeplable:run', ['locale' => $targetLanguage, '--force' => (bool) $attributes->force]);

        return response()->success(__lit(
            'deeplable.messages.translated',
            [
                'target' => $targetLanguage,
                'source' => $sourceLanguage,
            ]
        ));
    }
}
