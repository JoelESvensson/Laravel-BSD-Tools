<?php

namespace JoelESvensson\LaravelBsdTools\Api;

use Blue\Tools\Api\DeferredException;
use InvalidArgumentException;
use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;
use DOMDocument;

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


    public function create(string $name, string $slug)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $api = $dom->createElement('api');
        $consGroup = $dom->createElement('cons_group');

        $name = $dom->createElement('name', $name);
        $consGroup->appendChild($name);

        $slug = $dom->createElement('slug', $slug);
        $consGroup->appendChild($slug);

        $api->appendChild($consGroup);
        $dom->appendChild($api);
        return $this->api->post('cons_group/add_constituent_groups', [], $dom->saveXML());
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
