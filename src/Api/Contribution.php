<?php

namespace JoelESvensson\LaravelBsdTools\Api;

use Blue\Tools\Api\DeferredException;
use DateTime;
use InvalidArgumentException;
use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;

class Contribution
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
     * @param DateTime dateFilter
     */
    public function getContributions(string $type = null, $dateFilter = null)
    {
        if (!$type) {
            $type = 'all';
        }

        $filter = [];

        if (!$dateFilter) {
            $dateFilter = 'past24hours';
        } elseif (is_array($dateFilter)) {
            $filter['start'] = $dateFilter['start']->format('Y-m-d H:i:s');
            $filter['stop'] = $dateFilter['stop']->format('Y-m-d H:i:s');
            $dateFilter = 'custom';
        }

        $filter['type'] = $type;
        $filter['date'] = $dateFilter;
        try {
            $this->api->setDeferredResultMaxAttempts(60);
            $this->api->setDeferredResultInterval(10);
            $response = $this->api->get('contribution/get_contributions', [
                'filter' => $filter
            ]);
        } finally {
            $this->api->setDeferredResultMaxAttempts(20);
            $this->api->setDeferredResultInterval(5);
        }

        return $response;
    }
}
