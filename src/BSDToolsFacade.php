<?php

namespace JoelESvensson\LaravelBsdTools;

use Illuminate\Support\Facades\Facade;

class BsdToolsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BsdTools::class;
    }
}
