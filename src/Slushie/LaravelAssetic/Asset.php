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
use Assetic\Filter\FilterInterface;
use Assetic\FilterManager;
use Config;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use URL;

/**
 * Provides a front-end for Assetic collections.
 *
 * @package Slushie\LaravelAssetic
 */
class Asset {
  public $group = array();

  /** @var FilterManager */
  public $filters;

  /** @var AssetManager */
  public $assets;

  protected $namespace = 'laravel-assetic';

  public function __construct() {
    $this->createFilterManager();
    $this->createAssetManager();
  }

  /**
   * Create a new AssetCollection instance for the given group.
   *
   * @param string $name
   * @return \Assetic\Asset\AssetCollection
   */
  public function group($name) {
    if (isset($this->groups[$name])) {
      return $this->groups[$name];
    }

    $assets = $this->createAssetArray($name);
    $filters = $this->createFilterArray($name);
    $coll = new AssetCollection($assets, $filters);

    if ($output = $this->getConfig($name, 'output')) {
      $coll->setTargetPath($output);
    }

    // output to public by default
    $output = $coll->getTargetPath();
    if (!starts_with($output, '/')) {
      $output = public_path($output);
    }

    // check output cache
    $write_output = true;
    if (file_exists($output)) {
      $output_mtime = filemtime($output);
      $asset_mtime = $coll->getLastModified();

      if ($asset_mtime && $output_mtime >= $asset_mtime) {
        $write_output = false;
      }
    }

    if ($write_output) {
      file_put_contents($output, $coll->dump());
    }

    return $this->group[$name] = $coll;
  }

  /**
   * Treat group names as dynamic properties.
   *
   * @param $name
   * @return AssetCollection
   */
  public function __get($name) {
    return $this->group($name);
  }

  /**
   * Generate the URL for a given asset group.
   *
   * @param $name
   * @return string
   */
  public function url($name) {
    $group = $this->group($name);
    return URL::asset($group->getTargetPath());
  }

  /**
   * Create an array of AssetInterface objects for a group.
   * @param $name
   * @return array
   */
  protected function createAssetArray($name) {
    $config = $this->getConfig($name, 'assets', array());
    $assets = array();
    foreach ($config as $asset) {
      $assets[] = $this->assets->get($asset);
    }

    return $assets;
  }

  /**
   * Create an array of FilterInterface objects for a group.
   *
   * @param $name
   * @return array
   */
  protected function createFilterArray($name) {
    $config = $this->getConfig($name, 'filters', array());
    $filters = array();
    foreach ($config as $filter) {
      $filters[] = $this->filters->get($filter);
    }

    return $filters;
  }

  /**
   * Creates the filter manager from the config file's filter array.
   *
   * @return FilterManager
   */
  protected function createFilterManager() {
    $manager = new FilterManager();
    $filters = Config::get($this->namespace . '::filters', array());
    foreach ($filters as $name => $filter) {
      $manager->set($name, $this->createFilter($filter));
    }

    return $this->filters = $manager;
  }

  /**
   * Create a filter object from a value in the config file.
   *
   * @param callable|string|FilterInterface $filter
   * @return FilterInterface
   * @throws \InvalidArgumentException when a filter cannot be created
   */
  protected function createFilter($filter) {
    if (is_callable($filter)) {
      return call_user_func($filter);
    }
    else if (is_string($filter)) {
      return new $filter;
    }
    else if (is_object($filter)) {
      return $filter;
    }
    else {
      throw new \InvalidArgumentException("Cannot convert $filter to filter");
    }
  }

  protected function createAssetManager() {
    $manager = new AssetManager;
    $config = Config::get($this->namespace . '::assets', array());

    foreach ($config as $key => $refs) {
      if (!is_array($refs)) {
        $refs = array($refs);
      }

      $asset = array();
      foreach ($refs as $ref) {
        if (starts_with($ref, 'http://')) {
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
        $manager->set($key,
          count($asset) > 1
            ? new AssetCollection($asset)
            : $asset[0]
        );
      }
    }

    return $this->assets = $manager;
  }

  protected function getConfig($group, $key, $default = null) {
    return Config::get($this->namespace . "::groups.$group.$key", $default);
  }

}