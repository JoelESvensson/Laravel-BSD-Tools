<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Logging\Log;

class TryCache
{

    /**
     * @property Repository $cache
     */
    private $cache;

    /**
     * @property Log $log
     */

    public function __construct(Repository $cache, Log $log)
    {
        $this->cache = $cache;
        $this->log = $log;
    }

    public function __invoke(array $data): array
    {
        foreach ($data['prepared'] as $key => $value) {
            $hashKey = hash('sha256', json_encode($value['data']));
            $result = $this->cache->get($hashKey);
            if ($result !== null) {
                $data['done'][$key] = $value;
                $data['done'][$key]['hashKey'] = $hashKey;
                $data['done'][$key]['data'] = $result;
                $data['done'][$key]['cached'] = true;
                unset($data['prepared'][$key]);
                $this->log->debug('Cache hit', [
                    'date' => $key,
                ]);
            } else {
                $data['prepared'][$key]['hashKey'] = $hashKey;
            }
        }

        return $data;
    }
}
