<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use DateTime;
use DateInterval;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Logging\Log;

class ReturningForAction
{
    private $privateApi;
    private $log;
    public function __construct($privateApi, Log $log)
    {
        $this->privateApi = $privateApi;
        $this->log = $log;
    }

    private function data(
        DateTime $fromDate,
        DateTime $toDate
    ): array {
        $this->log->debug('Creating returning for action request', [
            'fromDate' => $fromDate->format('Y-m-d'),
            'toDate' => $toDate->format('Y-m-d'),
        ]);
        $options = [
            'dhc_cons_action[selected]' => '1',
            'dhc_row_counter[count]' => '1',
            'dhc_date_or_anytime[selected]' => '1',
            'dhc_row_counter[operator]' => 'gt',
            'dhc_date[start_dt][M]' => '1',
            'dhc_date[start_dt][d]' => '1',
            'dhc_date[start_dt][Y]' => '2016',
            'dhc_date[end_dt][M]' => '2',
            'dhc_date[end_dt][d]' => '1',
            'dhc_date[end_dt][Y]' => '2016',
            'dhc_cons_action[action][contribution]' => 0,
            'dhc_cons_action[action][invitation]' => 0,
            'dhc_cons_action[action][signup]' => 0,
        ];
        $preOptions = $options;
        $preOptions['dhc_date[start_dt][Y]'] = '2004';
        $preOptions['dhc_date[end_dt][Y]'] = $options['dhc_date[start_dt][Y]'] = (string)$fromDate->year;
        $preOptions['dhc_date[end_dt][M]'] = $options['dhc_date[start_dt][M]'] = (string)$fromDate->month;
        $preOptions['dhc_date[end_dt][d]'] = $options['dhc_date[start_dt][d]'] = (string)$fromDate->day;
        $options['dhc_date[end_dt][Y]'] = (string)$toDate->year;
        $options['dhc_date[end_dt][M]'] = (string)$toDate->month;
        $options['dhc_date[end_dt][d]'] = (string)$toDate->day;
        return [
            'action' => 'request_query_count',
            'req_args' => [
                'sqd' => [
                    'version' => '2',
                    'groups' => [
                        [
                            'type' => 'include',
                            'andor' => 'and',
                            'atoms' => [
                                [
                                    'type' => 'criterion',
                                    'slug' => 'has_action',
                                    'options' => $options,
                                    'primary_key' => 'cons_id',
                                ], [
                                    'type' => 'criterion',
                                    'slug' => 'has_action',
                                    'options' => $preOptions,
                                    'primary_key' => 'cons_id',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }


    public function __invoke(array $parameters): array
    {
        $fromDate = $parameters['fromDate'];
        $toDate = $parameters['toDate'];
        $interval = $parameters['interval'] ?? CarbonInterval::create(0, 0, 0, 1);
        $windowSize = $parameters['windowSize'] ?? $interval;

        $fromDate->sub($windowSize);
        $fromStep = clone $fromDate;
        $toStep = clone $fromDate;
        $toStep->add($windowSize);
        $data = [
            'prepared' => [],
            'ongoing' => [],
            'completed' => [],
            'done' => [],
        ];
        for (;;) {
            if ($toStep->gte($toDate)) {
                break;
            }

            $data['prepared'][$toStep->format('Y-m-d')] = [
                'data' => $this->data(
                    $fromStep,
                    $toStep
                )
            ];
            $fromStep->add($interval);
            $toStep->add($interval);
        }

        return $data;
    }
}
