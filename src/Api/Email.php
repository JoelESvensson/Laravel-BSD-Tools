<?php

namespace JoelESvensson\LaravelBsdTools\Api;

use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;

class Email
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
    public function unsubscribe($params, string $reason = null)
    {
        if (is_string($params)) {
            $params = ['email' => $params];
        } elseif (!is_array($params)) {
            throw new InvalidArgumentException();
        }

        if ($reason) {
            $params['reason'] = $reason;
        }

        $result = $this->api->post(
            'cons/email_unsubscribe',
            $params
        );
        return $result;
    }

    /**
     * @param array|string $params May either take all params as an array or
     * the email as a string
     * @param ?string $reason Won't be used if $params is an array
     */
    public function register($params, bool $subscribed = true)
    {
        if (is_string($params)) {
            $params = ['email' => $params];
        } elseif (!is_array($params)) {
            throw new InvalidArgumentException();
        }

        if ($subscribed) {
            $params['is_subscribed'] = 1;
        }

        $result = $this->api->post(
            'cons/email_register',
            $params
        );
        return $result;
    }
}
