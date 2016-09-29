<?php

namespace JoelESvensson\LaravelBsdTools\Api;

use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;

class SignupForm
{

    /**
     * @var ApiClient
     */
    private $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * @param array|string $params
     * @param ?string $reason
     */
    public function list()
    {
        return $this->api->get('signup/list_forms');
    }
}
