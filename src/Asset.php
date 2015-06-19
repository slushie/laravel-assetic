<?php namespace Slushie\LaravelAssetic;

use InvalidArgumentException;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Filter\FilterInterface;
use Assetic\FilterManager;
use Config;
use URL;

class Asset
{
    public $groups = [];

    /**
     * @var FilterManager
     */
    public $filters;

    /**
     * @var AssetManager
     */
    public $assets;

    protected $md5;
    protected $secure;

    public function __construct()
    {
        $this->createFilterManager();
        $this->createAssetManager();

        $this->md5 = $this->getConfig('md5', true);
        $this->secure = $this->getConfig('secure', true);
    }

    /**
     * Create a new AssetCollection instance for the given group.
     *
     * @param  string                         $name
     * @param  bool                           $overwrite force writing
     * @return \Assetic\Asset\AssetCollection
     */
    public function createGroup($name, $overwrite = false)
    {
        if (isset($this->groups[$name])) {
            return $this->groups[$name];
        }

        $assets = $this->createAssetArray($name);
        $filters = $this->createFilterArray($name);
        $collection = new AssetCollection($assets, $filters);

        if ($output = $this->getGroupConfig($name, 'output')) {
            $collection->setTargetPath($output);
        }

        // Check output cache
        $writeOutput = true;
        if (!$overwrite) {
            if (file_exists($output = public_path($collection->getTargetPath()))) {
                $outputTime = filemtime($output);
                $assetTime = $collection->getLastModified();

                if ($assetTime && $outputTime >= $assetTime) {
                    $writeOutput = false;
                }
            }
        }

        // Store assets
        if ($overwrite || $writeOutput) {
            $writer = new AssetWriter(public_path());
            $writer->writeAsset($collection);
        }

        return $this->groups[$name] = $collection;
    }

    /**
     * Treat group names as dynamic properties.
     *
     * @param  $name
     * @return AssetCollection
     */
    public function __get($name)
    {
        return $this->createGroup($name);
    }

    /**
     * Generate the URL for a given asset group.
     *
     * @param  $name
     * @param  array  $options options: array(secure => bool, md5 => bool)
     * @return string
     */
    public function url($name, array $options = null)
    {
        $options = is_null($options) ? [] : $options;
        $group = $this->createGroup($name);

        $cache_buster = '';
        if (array_get($options, 'md5', $this->md5)) {
            $cache_buster = '?'.md5_file($this->file($name));
        }

        $secure = array_get($options, 'secure', $this->secure);

        return URL::asset($group->getTargetPath(), $secure).$cache_buster;
    }

    /**
     * Get the output filename for an asset group.
     *
     * @param  $name
     * @return string
     */
    public function file($name)
    {
        $group = $this->createGroup($name);

        return public_path($group->getTargetPath());
    }

    /**
     * Returns an array of group names.
     *
     * @return array
     */
    public function listGroups()
    {
        $groups = $this->getConfig('groups', []);

        return array_keys($groups);
    }

    /**
     * Create an array of AssetInterface objects for a group.
     *
     * @param  $name
     * @throws \InvalidArgumentException for undefined assets
     * @return array
     */
    protected function createAssetArray($name)
    {
        $config = $this->getGroupConfig($name, 'assets', []);
        $assets = [];

        foreach ($config as $asset) {
            if ($this->assets->has($asset)) {
                // Existing asset definition
                $assets[] = $this->assets->get($asset);
            } elseif (str_contains($asset, array('/', '.', '-'))) {
                // Looks like a file
                $assets[] = $this->parseAssetDefinition($asset);
            } else {
                // Unknown asset
                throw new InvalidArgumentException("No asset '$asset' defined");
            }
        }

        return $assets;
    }

    /**
     * Create an array of FilterInterface objects for a group.
     *
     * @param  $name
     * @return array
     */
    protected function createFilterArray($name)
    {
        $config = $this->getGroupConfig($name, 'filters', []);
        $filters = [];

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
    protected function createFilterManager()
    {
        $manager = new FilterManager();
        $filters = $this->getConfig('filters', []);

        foreach ($filters as $name => $filter) {
            $manager->set($name, $this->createFilter($filter));
        }

        return $this->filters = $manager;
    }

    /**
     * Create a filter object from a value in the config file.
     *
     * @param  callable|string|FilterInterface $filter
     * @return FilterInterface
     * @throws \InvalidArgumentException       when a filter cannot be created
     */
    protected function createFilter($filter)
    {
        if (is_callable($filter)) {
            return call_user_func($filter);
        } elseif (is_string($filter)) {
            return new $filter();
        } elseif (is_object($filter)) {
            return $filter;
        } else {
            throw new InvalidArgumentException("Cannot convert $filter to filter");
        }
    }

    protected function createAssetManager()
    {
        $manager = new AssetManager();
        $config = $this->getConfig('assets', []);

        foreach ($config as $key => $refs) {
            if (!is_array($refs)) {
                $refs = array($refs);
            }

            $assets = [];

            foreach ($refs as $ref) {
                $assets[] = $this->parseAssetDefinition($ref);
            }

            if (count($assets) > 0) {
                $asset = count($assets) > 1 ? new AssetCollection($assets) : $assets[0];
                $manager->set($key, $asset);
            }
        }

        return $this->assets = $manager;
    }

    /**
     * Create an asset object from a string definition.
     *
     * @param  string         $asset
     * @return AssetInterface
     */
    protected function parseAssetDefinition($asset)
    {
        if (starts_with($asset, 'http://')) {
            return new HttpAsset($asset);
        } elseif (str_contains($asset, array('*', '?'))) {
            return new GlobAsset($this->absolutePath($asset));
        } else {
            return new FileAsset($this->absolutePath($asset));
        }
    }

    protected function getGroupConfig($group, $key, $default = null)
    {
        return $this->getConfig("groups.$group.$key", $default);
    }

    protected function getConfig($key, $default = null)
    {
        return Config::get("laravel-assetic.$key", $default);
    }

    /**
     * Returns the absolute path for a string. Relative paths are made
     * absolute relative to the public folder. Absolute paths are
     * returned without change.
     *
     * @param  string $relativeOrAbsolute
     * @return string
     */
    protected function absolutePath($relativeOrAbsolute)
    {
        // Already absolute if path starts with / or drive letter
        if (preg_match(',^([a-zA-Z]:|/),', $relativeOrAbsolute)) {
            return $relativeOrAbsolute;
        }

        return public_path($relativeOrAbsolute);
    }
}
