Laravel-Assetic
===============

Integrate Assetic(https://github.com/kriswallsmith/assetic) with Laravel 4 . Using this pacakage you can easily integrate Assetic in Laravel. You can use all the features what are there in Assetic.


Key Features : 
--------------
1. Assets can be easily maintained within Groups
2. Instant single file compilation,concatenation and minfication
3. Multiple filters can be applied to single group
4. Automatic file changes reflect using css-rewrite filter
5. Assets can be compiled in command-line using php artisan asset:warm --overwrite

Usage :
-------

Basic usage is relatively straightforward. The package should be installed by adding it to your composer.json like

    "require": {
       "laravel/framework": "4.0.*",
       "slushie/laravel-assetic": "dev-master",
       "lmammino/jsmin4assetic": "1.0.0",
       "leafo/lessphp": "0.4.0"
     },


After composer update you need to add service provider in your `app/config/app.php` file as

    'providers' => array (
        ...
        'Slushie\LaravelAssetic\LaravelAsseticServiceProvider',
        ...
    ),
    'aliases' => array (
        ...
        'Asset'           => 'Slushie\LaravelAssetic\Facades\AssetFacade',
        ...
    ),
    
Once it has been updated, then generate the config.php file from the package as:

    php artisan config:publish slushie/laravel-assetic


 then laravel-assetic Configuration file will be generated at : 

    app/config/packages/slushie/laravel-assetic/config.php

Next, edit the configuration file  `app/config/packages/slushie/assetic/config.php` file to
define your assets. You can define multiple groups, each with different filters and assets.


Defineing filters:
-----------------
First open the configuration file . Here you need to define filters. You can define multiple filters also

For example,

      'filters' => array(
    // filter with a closure constructor
    'yui_js' => function() {
      return new Assetic\Filter\Yui\JsCompressorFilter('yui-compressor.jar');
    },
    // filter with a simple class name
    'js_min'      => 'Assetic\Filter\JSMinFilter',
    'css_import'  => 'Assetic\Filter\CssImportFilter',
    'css_min'     => 'Assetic\Filter\CssMinFilter',
    'css_rewrit'  => 'Assetic\Filter\CssRewriteFilter',
    'emed_css'    => 'Assetic\Filter\PhpCssEmbedFilter',
    'coffe_script'=> 'Assetic\Filter\CoffeeScriptFilter',
    'less_php'    => 'Assetic\Filter\LessphpFilter',
    ),


Adding Assets into Groups:
-------------

In configuration file you can simply add css and js by creating different assets groups Next, just call the group name in views so  that a single file will be  compiled and loaded in views.

For example,

you can specify diffent asset gropus as :

    'groups' => array(

      /*
      * If you want add a folder then mention folderpath/*extension
      * By Default it will take public path. You can also mention base path.
      * Mention output file name  where scripts should compile...
      * Finally add group name in views as
      * <script src="<?php echo Asset::url('singlejs-main'); ?>"></script>
      */

     // Adding js to singlejs-mainjs group

    'singlejs-main' => array(
      // named filters are defined below
      'filters' => array(
        'js_min' // Here you need to mention filter name
      ),

      // named assets defined below
      'assets' => array(
        'assets/javascripts/coolarize/*js',
        'assets/javascripts/common/search.js',
        'assets/javascripts/nect/next.js'
        // its also possible to include assets here directly
        // eg, public_path('jquery-ui.js')
      ),

      // output path (probably relative to public)
      // must be rewritable
      'output' => 'singlejs-main.js'
    ),

     // Adding css to singlecss-main group

    'singlecss-main' => array(
      // you define multiple filters in array
      'filters' => array(
        'css_import',
        'css_rewrit',
        'css_min'
      ),

      // named assets defined below
      'assets' => array(
        'assets/stylesheets/frontend/font-awesome/css/font-awesome.css',
        'assets/stylesheets/frontend/prettyPhoto/css/*css',
        'assets/stylesheets/frontend/bootstrap/css/bootstrap.min.css'

        // its also possible to include assets here directly
        // eg, public_path('jquery-ui.js')
      ),

      // output path (probably relative to public)
      // must be rewritable
      'output' => 'singlecss-main.css'
    ),

    
    // adding less files to bast-public-less group

    'bast-public-less' => array(
      // named filters are defined below
      'filters' => array(
        'css_import',
        'css_rewrit',
        'less_php',
        'css_min'
      ),

      // named assets defined below
      'assets' => array(
        'assets/css/less/master.less'
      ),

      // output path (probably relative to public)
      // must be rewritable
      'output' => 'bast-public-less.css'
            ),
       ),

Using in Views:
--------------
This group can then be accessed from within your views using the `Asset` facade. To
link to the script group, you can use the `Asset::url()` method as follows:

    <script src="<?php echo Asset::url('singlejs-main'); ?>"></script>

Configuration file is more detailed with many examples, even youcan edit them as per your needs


This will output the URL to the asset group (in this case, probably `/singlejs-main.js`) and
simultaneously generate the file using Assetic, including joining all files and
running whatever filters you've defined.

You can also generate the asset output files via the artisan command:

    > php artisan asset:warm

Of course, this can be performed as a composer post-install command to generate
assets at deployment time.

More Information:
----------------

More information can be acquired by reading through the source, which is
fully documented, or you may feel free to raise issues at https://github.com/aditya-/laravel-assetic/
