<?php

namespace JoelESvensson\LaravelBsdTools\Api;

use Blue\Tools\Api\DeferredException;
use InvalidArgumentException;
use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;

class ConstituentGroup
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
        return $this->api->post(
            'cons_group/get_constituent_group',
            ['cons_group_id' => $id]
        );
    }

    private function bySlug(string $slug)
    {
        return $this->api->post(
            'cons_group/get_constituent_group_by_slug',
            ['slug' => $slug]
        );
    }

    public function get($param)
    {
        if (is_int($param)) {
            return $this->byId($param);
        } elseif (is_string($param)) {
            return $this->bySlug($param);
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @param array|string $params
     * @param ?string $reason
     */
    public function list()
    {
        return $this->api->get('cons_group/list_constituent_groups');
    }
}
