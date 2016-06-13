<?php

namespace JoelESvensson\LaravelBsdTools\Facades;

use JoelESvensson\LaravelBsdTools\PrivateApi\Client;
use Illuminate\Support\Facades\Facade;

class PrivateApiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
