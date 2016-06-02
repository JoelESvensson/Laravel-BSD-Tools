<?php

namespace JoelESvensson\LaravelBsdTools;

use Illuminate\Support\ServiceProvider;

class BsdToolsServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('bsdtools.php')
        ]);
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BsdTools::class, function ($app) {
            return new BsdTools(config('bsdtools'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [BsdTools::class];
    }
}
