# Litstack Deeplable - DeepL Translator

A package to automatically translate [CRUD models](https://litstack.io/docs/crud/model) and [forms](https://litstack.io/docs/crud/forms) in litstack via the DeepL api.

## Setup

Install the package via composer.

```shell
composer require litstack/deeplable
```

Please follow the steps for setting up the [aw-studio/laravel-deeplable](https://github.com/aw-studio/laravel-deeplable) package.

## Usage

The package ships with 2 actions:

-   `Litstack\Deeplable\TranslateAction` - Translates a single model/form
-   `Litstack\Deeplable\TranslateAllAction` - Translates all models configured in the `deeplable.models` config

Example:

```php
$page->headerLeft()->action('Übersetzen', TranslateAction::class);
```

You may add the following models to the `deeplable.models` config:

-   `Ignite\Crud\Models\Form`
-   `Ignite\Crud\Models\Repeatable`
