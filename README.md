# Laravel-Assetic

Easily integrate [Assetic](https://github.com/kriswallsmith/assetic) with Laravel 4 or 5.

[![Total Downloads](https://poser.pugx.org/slushie/laravel-assetic/downloads.svg)](https://packagist.org/packages/slushie/laravel-assetic)
[![Latest Stable Version](https://poser.pugx.org/slushie/laravel-assetic/v/stable.svg)](https://packagist.org/packages/slushie/laravel-assetic)
[![Latest Unstable Version](https://poser.pugx.org/slushie/laravel-assetic/v/unstable.svg)](https://packagist.org/packages/slushie/laravel-assetic)
[![License](https://poser.pugx.org/slushie/laravel-assetic/license.svg)](https://packagist.org/packages/slushie/laravel-assetic)

## Key Features

1. Easily maintain assets within groups.
2. Instant single file compilation, concatenation, and minification.
3. Apply multiple filters to each group.
4. Automatically updates output files when their inputs have been changed.
5. Pre-compile assets using `artisan asset:warm`.

## Contents

- [Installation](#install)
- [Groups](#groups)
- [Filters](#filters)
- [Views](#views)
- [More Information](#more-information)

## Installation

The recommended way to install is through [Composer](http://getcomposer.org).

Update the project's composer.json file to include Laravel-Assetic:

- Laravel 5: use the 2.\* releases.
- **Laravel 4: use the 1.\* releases**.

```json
{
    "require": {
        "slushie/laravel-assetic": "1.*"
    }
}
```

Then update the project dependencies to include this library:

```bash
composer update slushie/laravel-assetic
```

After installing, add the service provider and optional `Asset` facade in `config/app.php`:

```php
'providers' => [
    ...
    'Slushie\LaravelAssetic\LaravelAsseticServiceProvider',
    ...
],
'aliases' => [
    ...
    'Asset' => 'Slushie\LaravelAssetic\Facades\Asset',
    ...
],
```

Once the app's configuration has been updated, publish the package config:

```bash
php artisan vendor:publish
```

Now the laravel-assetic configuration file will be available at:

```
config/laravel-assetic.php
```

Finally, edit the configuration file to define your assets.
You can define multiple groups, each with different filters and assets.

## Groups

Each group defines `assets` and `filters` as inputs, and an `output` file that should be included in your view.

```php
'groups' => array(
    'main_js' => array(
        'filters' => array(
            'js_min',
        ),
        'assets' => array(
            'jquery',                       // Named asset defined below
            'assets/js/common/search.js',   // Single file
            'assets/js/coolarize/*js',      // Folder inclusion
        ),
        'output' => 'scripts.js',           // Writable output relative to public_path()
    ),
),
```

## Filters

Filters are defined within the package configuration file.
They are applied to the supplied asset files.
Use a closure to instantiate filters that may not have default constructor arguments.

```php
'filters' => array(
    'css_min'       => 'Assetic\Filter\CssMinFilter',
    'css_import'    => 'Assetic\Filter\CssImportFilter',
    'css_rewrite'   => 'Assetic\Filter\CssRewriteFilter',
    'embed_css'     => 'Assetic\Filter\PhpCssEmbedFilter',
    'less_php'      => 'Assetic\Filter\LessphpFilter',
    'js_min'        => 'Assetic\Filter\JSMinFilter',
    'coffee_script' => 'Assetic\Filter\CoffeeScriptFilter',
    'yui_js' => function () {
        return new Assetic\Filter\Yui\JsCompressorFilter('yui-compressor.jar');
    },
),
```

## Views

Once defined, your groups can then be accessed from within your views using the `Asset` facade.
To link to the `main_js` group, use the `Asset::url()` method as follows:

```html
<script src="<?= Asset::url('main_js'); ?>"></script>
```

This will output the URL to the asset file (in this example, `/scripts.js`).

Options can be set so the asset generated is linked via `https://` or appended with a cache busting key:

```html
<script src="<?= Asset::url('main_js', ['md5' => true, 'secure' => true]); ?>"></script>
```

When the page loaded, Assetic will generate the file, joining all files and running the defined filters.

You can also pre-generate the asset output files via the artisan command:

```bash
php artisan asset:warm
```

Of course, this can be performed as a composer `post-install` command to generate assets at deployment time.

# More Information

More information can be acquired by reading through the source, which is fully documented.
Feel free to raise issues at https://github.com/slushie/laravel-assetic/issues.
