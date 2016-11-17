<?php

namespace JoelESvensson\LaravelBsdTools\Facades;

use JoelESvensson\LaravelBsdTools\Api\Contribution;
use Illuminate\Support\Facades\Facade;

class ContributionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Contribution::class;
    }
}
