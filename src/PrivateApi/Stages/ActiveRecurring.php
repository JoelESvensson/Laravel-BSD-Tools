<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use DateTime;
use DateInterval;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Logging\Log;

class ActiveRecurring
{
    private $privateApi;
    private $log;
    public function __construct($privateApi, Log $log)
    {
        $this->privateApi = $privateApi;
        $this->log = $log;
    }

    private function data(): array
    {
        $this->log->debug('Creating active recurring count request');
        return [
            'action' => 'request_query_count',
            'req_args' => [
                'sqd' => [
                    'version' => '2',
                    'groups' => [
                        [
                            'type' => 'include',
                            'andor' => 'or',
                            'atoms' => [
                                [
                                    'type' => 'criterion',
                                    'slug' => 'recurring_contributor',
                                    'options' => ['selected' => '2'],
                                    'primary_key' => 'cons_id',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }


    public function __invoke(array $parameters): array
    {
        $data = [
            'prepared' => [],
            'ongoing' => [],
            'completed' => [],
            'done' => [],
        ];
        $data['prepared'][] = [
            'data' => $this->data(),
            'cacheDuration' => 10,
        ];

        return $data;
    }
}
