<?php

namespace JoelESvensson\LaravelBsdTools\Facades;

use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;
use Illuminate\Support\Facades\Facade;

class ApiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ApiClient::class;
    }
}
