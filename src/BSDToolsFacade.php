<?php 

namespace Midvinter\BSDTools;

use Illuminate\Support\Facades\Facade;

class BSDToolsFacade extends Facade {
    
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return BSDTools::class;
    }

}
