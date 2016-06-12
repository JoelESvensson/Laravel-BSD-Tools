<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

class BeginCount
{
    private $privateApi;
    public function __construct($privateApi)
    {
        $this->privateApi = $privateApi;
    }

    private function request(array $requestData)
    {
        $requestData['uid'] = $this->privateApi->searchSession();
        return $this->privateApi->post(
            '/utils/query_builder/admin/query_builder.ajax.php',
            [
                'form_params' => [
                    'req' => json_encode($requestData),
                ],
                'headers' => [
                    'Referer' => $this->privateApi->url.'/admin/Constituent/ConsSearch',
                ],
            ]
        );
    }

    public function __invoke(array $data): array
    {
        foreach ($data['ongoing'] as $key => $value) {
            $json = json_decode(
                (string)$this->request(
                    $value['data']
                )->getBody(),
                true
            );
            $searchId = $json['search_id'];
            unset($json);
            $data['ongoing'][$key]['data'] = $searchId;
            echo "$key - $searchId\n";
        }

        return $data;
    }
}
