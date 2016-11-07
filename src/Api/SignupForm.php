<?php
declare(strict_types=1);

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

    private function byId(int $id)
    {
        return $this->api->get('signup/get_form', ['signup_form_id' => $id]);
    }

    public function get($id, array $options = null)
    {
        if (is_string($id)) {
            $id = (int)$id;
        } elseif (!is_int($id)) {
            throw new InvalidArgumentException();
        }

        return $this->byId($id);
    }

    /**
     * @param array|string $params
     * @param ?string $reason
     */
    public function list()
    {
        return $this->api->get('signup/list_forms');
    }

    public function fastCount(int $id)
    {
        return $this->api->signupCount($id);
    }
}
