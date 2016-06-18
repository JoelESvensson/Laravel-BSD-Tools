<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Illuminate\Contracts\Cache\Repository;

class Cache
{

    /**
     * @param Repository $cache
     */
    private $cache;

    private $durationInMinutes;
    public function __construct(
        Repository $cache,
        int $durationInMinutes = 0
    ) {
        $this->cache = $cache;
        $this->durationInMinutes = $durationInMinutes;
    }

    public function __invoke(array $data): array
    {
        foreach ($data['done'] as $key => $value) {
            $cacheDuration = $value['cacheDuration'] ?? $this->durationInMinutes;
            if ($cacheDuration) {
                $this->cache->put(
                    $value['hashKey'],
                    $value['data'],
                    $cacheDuration
                );
            } else {
                $this->cache->forever($value['hashKey'], $value['data']);
            }
        }

        return $data;
    }
}
