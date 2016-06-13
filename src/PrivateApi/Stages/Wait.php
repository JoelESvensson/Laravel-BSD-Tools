<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Illuminate\Contracts\Logging\Log;

class Wait
{

    private $privateApi;
    private $log;
    public function __construct($privateApi, Log $log)
    {
        $this->privateApi = $privateApi;
        $this->log = $log;
    }

    public function __invoke(array $data): array
    {
        while (!empty($data['ongoing'])) {
            sleep(5);
            foreach ($data['ongoing'] as $key => $value) {
                $searchId = $value['data'];
                $response = $this->privateApi->post(
                    '/utils/query_builder/admin/query_builder.ajax.php',
                    [
                        'form_params' => [
                            'req' => json_encode([
                                'uid' => $this->privateApi->searchSession(),
                                'action' => 'poll_query_results',
                                'req_args' => [
                                    'search_id' => $searchId,
                                ],
                            ])
                        ],
                        'headers' => [
                            'Referer' => $this->privateApi->url.'/admin/Constituent/ConsSearch',
                        ],
                    ]
                );
                $json = json_decode((string)$response->getBody(), true);
                switch ($json['status']) {
                    case 'counting':
                        break;
                    case 'complete':
                        $data['completed'][$key] = $data['ongoing'][$key];
                        unset($data['ongoing'][$key]);
                        $this->log->debug('Count is complete', [
                            'date' => $key,
                            'searchId' => $searchId,
                        ]);
                        break;
                    default:
                        unset($data['ongoing'][$key]);
                        $this->log->warning('Count failed', [
                            'date' => $key,
                            'searchId' => $searchId,
                            'status' => $json['status'],
                        ]);
                        break;
                }
            }
        }

        return $data;
    }
}
