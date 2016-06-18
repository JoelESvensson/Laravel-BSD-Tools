<?php

namespace JoelESvensson\LaravelBsdTools;

use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;
use JoelESvensson\LaravelBsdTools\Api\Constituent as ConstituentApi;
use JoelESvensson\LaravelBsdTools\Api\Email as EmailApi;
use JoelESvensson\LaravelBsdTools\PrivateApi\Client as PrivateApiClient;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\ServiceProvider;

class BsdServiceProvider extends ServiceProvider
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
        $this->app->singleton(ApiClient::class, function ($app) {
            return new ApiClient(config('bsdtools'));
        });
        $this->app->singleton(EmailApi::class);
        $this->app->singleton(ConstituentApi::class);
        $this->app->singleton(PrivateApiClient::class, function ($app) {
            return new PrivateClientApi(
                config('bsdtools'),
                $app->make(Repository::class),
                $app->make(Log::class)
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            ApiClient::class,
            EmailApi::class,
            ConstituentApi::class,
            PrivateApiClient::class,
        ];
    }
}
