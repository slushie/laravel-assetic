<?php
/**
 * Author: Josh Leder <slushie@gmail.com>
 * Created: 10/7/13 @ 11:24 AM
 */

namespace Slushie\LaravelAssetic;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;
use Assetic\FilterManager;
use Config;

/**
 * Provides a front-end for Assetic collections.
 *
 * @package Slushie\LaravelAssetic
 */
class Asset {
  public $groups = array();

  protected $namespace = 'laravel_assetic';
  protected $filters;
  protected $assets;

  public function __construct() {
    $this->createFilterManager();
    $this->createAssetManager();
  }

  /**
   * Treat group names as dynamic attributes.
   *
   * @param $name
   * @return AssetManager|null
   */
  public function __get($name) {
    if (isset($this->groups[$name]))
      return $this->groups[$name]->getAssetManager;

    return null;
  }


  /**
   * Create a new AssetFactory instance for the given name.
   *
   * @param string $name
   * @return \Assetic\Factory\AssetFactory
   */
  public function createGroup($name) {
    if (isset($this->groups[$name]))
      return $this->groups[$name];

    // create factory
    $path = public_path($this->getConfig($name, 'path', ''));
    $factory = new AssetFactory($path);

    // set managers
    $factory->setAssetManager($this->assets);
    $factory->setFilterManager($this->filters);

    // add assets to factory
    $assets = $this->getConfig($name, 'assets', array());
    $filters = $this->getConfig($name, 'filters', array());
    $factory->createAsset($assets, $filters);

    return $this->group[$name] = $factory;
  }

  protected function createFilterManager() {
    $filters = new FilterManager;
    $config = Config::get($this->namespace . '::filters', array());
    foreach ($config as $name => $class) {
      $filters->set($name, new $class);
    }

    return $this->filters = $filters;
  }

  protected function createAssetManager() {
    $assets = new AssetManager;
    $config = Config::get($this->namespace . '::assets', array());

    foreach ($config as $name => $refs) {
      if (!is_array($refs)) {
        $refs = array($refs);
      }

      $asset = array();
      foreach ($refs as $ref) {
        if (starts_with($ref, 'http:')) {
          $asset[] = new HttpAsset($ref);
        }
        else if (str_contains($ref, W('* ?'))) {
          $asset[] = new GlobAsset($ref);
        }
        else {
          $asset[] = new FileAsset($ref);
        }
      }

      if (count($asset) > 0) {
        $assets->set($name,
          count($asset) > 1
            ? new AssetCollection($asset)
            : $asset[0]
        );
      }
    }

    return $this->assets = $assets;
  }

  protected function getConfig($name, $key, $default = null) {
    return Config::get($this->namespace . "::groups.$name.$key", $default);
  }

}