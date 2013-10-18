laravel-assetic
===============
Integrate Assetic with Laravel 4

Usage
-----

Basic usage is relatively straightforward. The package should be installed
via composer, and the service provider registered in your `app/config/app.php` file.

Once it has been installed, extract the config.php file from the package:

  php artisan config:publish slushie/laravel-assetic

Next, edit the `app/config/packages/slushie/assetic/config.php` file to
define your assets. You can define multiple groups, each with different
filters and assets.

For example,

  'groups' => array(
    'script' => array(
      'filters' => array( 'js_min' ), // named filters are defined separately
      'assets' => array(
        'jquery',                     // named assets are defined separately
        'script/*js'                  // file and glob assets can be defined inline
      ),
      'output' => 'script.js'         // output is relative to public
    )

This group can then be accessed from within your views using the `Asset` facade. To
link to the script group, you can use the `Asset::url()` method as follows:

  <script src="<?php echo Asset::url('script'); ?>"></script>

This will output the URL to the asset group (in this case, probably `/script.js`) and
simultaneously generate the file using Assetic, including joining all files and
running whatever filters you've defined.

You can also generate the asset output files via the artisan command:

  > php artisan asset:warm

Of course, this can be performed as a composer post-install command to generate
assets at deployment time.

More Information
----------------

More information can be acquired by reading through the source, which is
fully documented, or asking on the Laravel forums.