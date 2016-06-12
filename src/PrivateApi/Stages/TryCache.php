<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Illuminate\Contracts\Cache\Repository;

class TryCache
{

    /**
     * @param Repository $cache
     */
    private $cache;
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function __invoke(array $counting): array
    {
        $data = [
            'ongoing' => [],
            'done' => [],
        ];
        foreach ($counting as $key => $value) {
            $hashKey = hash('sha256', json_encode($value));
            $result = $this->cache->get($hashKey);
            if ($result !== null) {
                $data['done'][$key] = [
                    'hashKey' => $hashKey,
                    'data' => $result,
                    'cached' => true,
                ];
            } else {
                $data['ongoing'][$key] = [
                    'hashKey' => $hashKey,
                    'data' => $value
                ];
            }
        }

        return $data;
    }
}
