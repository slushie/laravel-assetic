<?php
/**
 * Configure laravel-assetic options in this file.
 *
 * @package slushie/laravel-assetic
 */

return array(
  /*
   * Groups are named settings containing assets and filters,
   * plus an output file.
   */
  'groups' => array(
    'script' => array(
      // named filters are defined below
      'filters' => array(
        'js_min'
      ),

      // named assets defined below
      'assets' => array(
        'jquery',
        // its also possible to include assets here directly
        // eg, public_path('jquery-ui.js')
      ),

      // output path (probably relative to public)
      // must be rewritable
      'output' => 'script.js'
    ),
  ),

  'filters' => array(
    // filter with a closure constructor
    'yui_js' => function() {
      return new Assetic\Filter\Yui\JsCompressorFilter('yui-compressor.jar');
    },

    // filter with a simple class name
    'js_min' => 'Assetic\Filter\JSMinFilter'
  ),

  'assets' => array(
    // name => absolute path to asset file
    'jquery' => public_path('script/jquery.js'),
  )
);
