<?php namespace Slushie\LaravelAssetic;

use Illuminate\Support\ServiceProvider;
use Slushie\LaravelAssetic\Console\WarmCommand;

class LaravelAsseticServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/config.php';
        $this->publishes([
            $configPath => config_path('laravel-assetic.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['asset'] = $this->app->share(function () {
            return new Asset();
        });

        $this->app['command.asset.warm'] = $this->app->share(function () {
            return new WarmCommand();
        });

        $this->commands('command.asset.warm');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['asset'];
    }
}
