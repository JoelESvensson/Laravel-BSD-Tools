<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use Illuminate\Contracts\Logging\Log;

class ForceCompleteCount
{
    private $privateApi;

    /**
     * @property Log $log
     */
    private $log;
    public function __construct($privateApi, Log $log)
    {
        $this->privateApi = $privateApi;
        $this->log = $log;
    }

    public function __invoke(array $data): array
    {
        foreach ($data['ongoing'] as $key => $value) {
            $response = $this->privateApi->post(
                '/utils/query_builder/admin/query_builder.ajax.php',
                [
                    'form_params' => [
                        'req' => json_encode([
                            'uid' => $this->privateApi->searchSession(),
                            'action' => 'retrieve_query_results',
                            'req_args' => [
                                'search_id' => $value['data'],
                            ],
                        ])
                    ],
                    'headers' => [
                        'Referer' => $this->privateApi->url.'/admin/Constituent/ConsSearch',
                    ],
                ]
            );
            $json = json_decode((string)$response->getBody(), true);
            if (is_string($json)) {
                $this->log->notice('Forced fetch failed', [
                    'date' => $key,
                    'response' => $json,
                ]);
                continue;
            }

            $data['done'][$key] = $data['ongoing'][$key];
            $data['done'][$key]['data'] = $json['unique_cons'];
            $this->log->debug('Forced search result fetched', [
                'date' => $key,
                'uniqueCons' => $json['unique_cons'],
            ]);
            unset($data['ongoing'][$key]);
        }

        return $data;
    }
}
