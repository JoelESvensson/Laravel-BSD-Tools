<?php

namespace JoelESvensson\LaravelBsdTools;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;

class PrivateApi
{
    private $url;
    private $email;
    private $password;
    private $sessionId;
    private $http;
    private $cookies;

    private function signIn()
    {

    }

    public function actionsAtInterval(
        DateTime $fromDate,
        DateTime $toDate = null,
        DateInterval $interval = null
    ) {
        if (!$toDate) {
            $toDate = Carbon::now();
        }

        if (!$interval) {
            $interval = CarbonInterval::create(0, 1);
        }

        $response = $this->http->get('/admin/Constituent/ConsSearch');
        $result = preg_match(
            '/(?:'.preg_quote('BSD.cons_search.set_uid("').')(.+?)(?:'.'\\")/',
            (string)$response->getBody(),
            $match
        );

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

        $array = [
            'uid' => $match[1],
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


        while ($fromDate->lt($toDate)) {
            $tmp = clone $fromDate;
            $preOptions['dhc_date[end_dt][Y]'] = $options['dhc_date[start_dt][Y]'] = (string)$fromDate->year;
            $preOptions['dhc_date[end_dt][M]'] = $options['dhc_date[start_dt][M]'] = (string)$fromDate->month;
            $preOptions['dhc_date[end_dt][d]'] = $options['dhc_date[start_dt][d]'] = (string)$fromDate->day;
            $fromDate->add($interval);
            $options['dhc_date[end_dt][Y]'] = (string)$fromDate->year;
            $options['dhc_date[end_dt][M]'] = (string)$fromDate->month;
            $options['dhc_date[end_dt][d]'] = (string)$fromDate->day;
            $array['req_args']['sqd']['groups'][0]['atoms'][0]['options'] = $options;
            $array['req_args']['sqd']['groups'][0]['atoms'][1]['options'] = $preOptions;
            $response = $this->http->post(
                '/utils/query_builder/admin/query_builder.ajax.php',
                [
                    'form_params' => [
                        'req' => json_encode($array),
                    ],
                    'headers' => [
                        'Referer' => $this->url.'/admin/Constituent/ConsSearch',
                    ],
                ]
            );
            $json = json_decode($response->getBody(), true);
            $searchId = $json['search_id'];
            do {
                sleep(5);
                $response = $this->http->post(
                    '/utils/query_builder/admin/query_builder.ajax.php',
                    [
                        'form_params' => [
                            'req' => json_encode([
                                'uid' => $match[1],
                                'action' => 'poll_query_results',
                                'req_args' => [
                                    'search_id' => $searchId,
                                ],
                            ])
                        ],
                        'headers' => [
                            'Referer' => $this->url.'/admin/Constituent/ConsSearch',
                        ],
                    ]
                );
                $json = json_decode((string)$response->getBody(), true);
            } while ($json['status'] === 'counting');
            $response = $this->http->post(
                '/utils/query_builder/admin/query_builder.ajax.php',
                [
                    'form_params' => [
                        'req' => json_encode([
                            'uid' => $match[1],
                            'action' => 'retrieve_query_results',
                            'req_args' => [
                                'search_id' => $searchId,
                            ],
                        ])
                    ],
                    'headers' => [
                        'Referer' => $this->url.'/admin/Constituent/ConsSearch',
                    ],
                ]
            );
            $json = json_decode((string)$response->getBody(), true);
            echo $tmp.' - '.$json['unique_cons']."\n";
        }

    }

    public function __construct(array $params)
    {
        $this->email = $params['PRIVATE_API_EMAIL'];
        $this->password = $params['PRIVATE_API_PASSWORD'];
        $this->url = $params['PRIVATE_API_URL'];

        $this->cookies = new CookieJar;
        $this->http = new GuzzleClient([
            'cookies' => $this->cookies,
            'base_uri' => $this->url,
        ]);
        $response = $this->http->post('ctl/Core/AdminLoginAjax', [
            'form_params' => [
                'email' => $this->email,
                'password' => $this->password,
                'nossl' => 0,
                'redirect' => '',
            ]
        ]);
    }
}
