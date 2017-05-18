## Installation

Install the package with composer

    composer require joelesvensson/laravelbsdtools

Then add

    JoelESvensson\LaravelBsdTools\BsdToolsServiceProvider::class

to your list of providers in the app.php config. When this is done you should also publish the bsdtools config file

    php artisan vendor:publish
