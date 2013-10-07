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
      // path is relative to the public path
      'path' => 'script',

      // filters are named below
      'filters' => array('js-min')
    ),

  ),

  /**
   * Define the filter names to be used in the group filter settings.
   */
  'filters' => array(
    'coffee'      => 'Assetic\Filter\CoffeeScriptFilter',
    'js-min'      => 'Assetic\Filter\JSMinFilter',
    'js-min+'     => 'Assetic\Filter\JSMinPlusFilter',
    'js-uglify'   => 'Assetic\Filter\UglifyJsFilter',
    'js-uglify2'  => 'Assetic\Filter\UglifyJs2Filter',
    'css-uglify'  => 'Assetic\Filter\UglifyCssFilter',
    'css-min'     => 'Assetic\Filter\CssMinFilter',
    'less-php'    => 'Assetic\Filter\LessphpFilter',
    'less'        => 'Assetic\Filter\LessFilter',
  ),

  /**
   * Assets can be named as well.
   */
  'assets' => array()
);