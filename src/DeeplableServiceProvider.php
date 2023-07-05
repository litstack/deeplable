<?php

namespace Litstack\Deeplable;

use Ignite\Crud\Models\Media;
use Ignite\Foundation\Litstack;
use Ignite\Translation\Translator;
use Ignite\Application\Application;
use Ignite\Crud\Models\LitFormModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use AwStudio\Deeplable\Translators\Resolver;
use Astrotomic\Translatable\Contracts\Translatable;

class DeeplableServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->callAfterResolving('deeplable.translator', function (Resolver $resolver) {
            $resolver->register(LitFormModel::class, function () {
                return new FormModelTranslator($this->app['deeplable.api']);
            });
            $resolver->register(Media::class, function () {
                return new MediaModelTranslator($this->app['deeplable.api']);
            });

            $resolver->strategy(function (Model $model) {
                if ($model instanceof LitFormModel) {
                    return LitFormModel::class;
                }
                if ($model instanceof Media) {
                    return Media::class;
                }

                return Translatable::class;
            });
        });

        $this->callAfterResolving('lit.translator', function (Translator $translator) {
            $translator->addPath(__DIR__.'/../lang');
        });
    }
}
