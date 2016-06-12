<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

class Wait
{
    private $privateApi;
    public function __construct($privateApi)
    {
        $this->privateApi = $privateApi;
    }

    public function __invoke(array $data): array
    {
        $data['completed'] = [];
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
                if ($json['status'] !== 'counting') {
                    $data['completed'][$key] = $data['ongoing'][$key];
                    unset($data['ongoing'][$key]);
                    echo "Counted $key - $searchId - {$json['status']}\n";
                }
            }
        }
        unset($data['ongoing']);
        return $data;
    }
}
