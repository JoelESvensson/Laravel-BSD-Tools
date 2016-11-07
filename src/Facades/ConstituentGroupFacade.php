<?php

namespace JoelESvensson\LaravelBsdTools\Facades;

use JoelESvensson\LaravelBsdTools\Api\ConstituentGroup;
use Illuminate\Support\Facades\Facade;

class ConstituentGroupFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConstituentGroup::class;
    }
}
