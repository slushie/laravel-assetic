<?php
/**
 * Configure laravel-assetic options in this file.
 *
 * @package slushie/laravel-assetic
 */

return array(
  /**
   * Define the default configurations to be created on start.
   */
  'groups' => array(
    'script' => array(
      // named filters are defined below
      'filters' => array(
        'js_min'
      ),

      // named assets defined below
      'assets' => array(
        'jquery'
      ),

      // optional output path, must be rewritable
      'output' => public_path('script.js')
    ),
  ),

  'filters' => array(
    // filter with a closure constructor
    'yui_js' => function() {
      return new Assetic\Filter\Yui\JsCompressorFilter('yui-compressor.jar');
    },

    // filter with a simple class name
    'js_min' => 'Assetic\Filter\JsMinFilter'
  ),

  'assets' => array(
    'jquery' => public_path('script/jquery.js'),
  )
);