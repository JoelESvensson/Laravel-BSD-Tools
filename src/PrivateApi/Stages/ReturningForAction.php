<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use DateTime;
use DateInterval;
use Carbon\CarbonInterval;

class ReturningForAction
{
    private $privateApi;
    public function __construct($privateApi)
    {
        $this->privateApi = $privateApi;
    }

    private function data(
        DateTime $fromDate,
        DateTime $toDate
    ) {
        echo "{$fromDate->format('Y-m-d')} - {$toDate->format('Y-m-d')}\n";
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


    public function __invoke(array $parameters)
    {
        $fromDate = $parameters['fromDate'];
        $toDate = $parameters['toDate'];
        $interval = $parameters['interval'];
        $toStep = clone $fromDate;
        $data = [];
        for (;;) {
            $fromStep = clone $toStep;
            $toStep->add($interval);
            if ($toStep->gt($toDate)) {
                $data[$fromStep->format('Y-m-d')] = $this->data(
                    $fromStep,
                    $toDate // We don't want to jump over the toDate
                );
                break;
            }
            $data[$fromStep->format('Y-m-d')] = $this->data(
                $fromStep,
                $toStep
            );
        }

        return $data;
    }
}
