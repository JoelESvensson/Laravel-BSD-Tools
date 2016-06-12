<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

class CompleteCount
{

    private $privateApi;
    public function __construct($privateApi)
    {
        $this->privateApi = $privateApi;
    }

    public function __invoke(array $data): array
    {
        $data['done'] = $data['done'] ?? [];
        foreach ($data['completed'] as $key => $value) {
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
            $data['done'][$key] = $data['completed'][$key];
            $data['done'][$key]['data'] = $json['unique_cons'];
            echo "$key - {$json['unique_cons']}\n";
        }
        unset($data['completed']);
        return $data;
    }
}
