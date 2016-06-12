<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Illuminate\Contracts\Cache\Repository;

class Cache
{

    /**
     * @param Repository $cache
     */
    private $cache;
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function __invoke(array $data): array
    {
        foreach ($data['done'] as $key => $value) {
            $this->cache->put($value['hashKey'], $value['data'], 60 * 24);
        }

        return $data;
    }
}
