<?php

namespace JoelESvensson\LaravelBsdTools;

use JoelESvensson\LaravelBsdTools\Api\Constituent as ConstituentApi;
use JoelESvensson\LaravelBsdTools\Api\Email as EmailApi;
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
        $this->app->singleton(EmailApi::class);
        $this->app->singleton(ConstituentApi::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [BsdTools::class, EmailApi::class, ConstituentApi::class];
    }
}
