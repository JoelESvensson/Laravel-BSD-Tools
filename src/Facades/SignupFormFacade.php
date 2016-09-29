<?php

namespace JoelESvensson\LaravelBsdTools\Facades;

use JoelESvensson\LaravelBsdTools\Api\Email;
use Illuminate\Support\Facades\Facade;

class SignupFormFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SignupForm::class;
    }
}
