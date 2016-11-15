<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi\Stages;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Logging\Log;

class BeginCount
{
    private $privateApi;

    /**
     * @var Log
     */
    private $log;
    public function __construct($privateApi, Log $log)
    {
        $this->privateApi = $privateApi;
        $this->log = $log;
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
        foreach ($data['prepared'] as $key => $value) {
            try {
                $json = json_decode(
                    (string)$this->request(
                        $value['data']
                    )->getBody(),
                    true
                );
                $searchId = $json['search_id'];
                unset($json);
                $data['ongoing'][$key] = $value;
                $data['ongoing'][$key]['data'] = $searchId;
                unset($data['prepared'][$key]);
                $this->log->debug(
                    'Count has begun',
                    [
                        'date' => $key,
                        'searchId' => $searchId,
                    ]
                );
            } catch (RequestException $e) {

                /**
                 * This may happen. Just skip that query and move on.
                 */
                $this->log->warning(
                    'Count failed to begin',
                    [
                        'message' => $e->getMessage(),
                        'date' => $key,
                        'searchId' => $searchId,
                    ]
                );
            }
        }

        return $data;
    }
}
