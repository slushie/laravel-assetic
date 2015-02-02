# Laravel-Assetic

Easily integrate [Assetic](https://github.com/kriswallsmith/assetic) with Laravel 4.

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

## Usage

Add to your composer.json:

```json
"require": {
    "laravel/framework": "4.0.*",
    "slushie/laravel-assetic": "dev-master",
    "lmammino/jsmin4assetic": "1.0.0",
    "leafo/lessphp": "0.4.0"
}
```

After running `composer update`, you need to add the service provider (and optionally, alias the `Asset` facade) in your `app/config/app.php` file:

```php
'providers' => array(
    ...
    'Slushie\LaravelAssetic\LaravelAsseticServiceProvider',
    ...
),

'aliases' => array(
    ...
    'Asset'           => 'Slushie\LaravelAssetic\Facades\AssetFacade',
    ...
),
```

Once your app's configuration has been updated, generate the package config:

```bash
php artisan config:publish slushie/laravel-assetic
```

Now the laravel-assetic configuration file will be available at:

```
app/config/packages/slushie/laravel-assetic/config.php
```

Finally, edit the configuration file file to define your assets.
You can define multiple groups, each with different filters and assets.

## Defining Filters

Filters are defined within the package configuration file.

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

## Adding Assets to Groups

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

## Using Assets in Views

Once defined, your groups can then be accessed from within your views using the `Asset` facade.
To link to the `main_js` group, you can use the `Asset::url()` method as follows:

```html
<script src="<?php echo Asset::url('main_js'); ?>"></script>
```

This will output the URL to the asset file (in this example, `/scripts.js`).

When the page loaded, Assetic will generate the file, joining all files and running the defined filters.

You can also generate the asset output files via the artisan command:

```bash
php artisan asset:warm
```

Of course, this can be performed as a composer `post-install` command to generate assets at deployment time.

# More Information

More information can be acquired by reading through the source, which is
fully documented, or you may feel free to raise issues at https://github.com/slushie/laravel-assetic/issues
