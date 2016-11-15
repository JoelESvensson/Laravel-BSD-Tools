<?php

namespace JoelESvensson\LaravelBsdTools\PrivateApi;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Logging\Log;
use InvalidArgumentException;
use JoelESvensson\LaravelBsdTools\PrivateApi\Stages\{ //@codingStandardsIgnoreLine
    ActiveRecurring,
    BeginCount,
    Cache,
    Callback,
    CompleteCount,
    ForceCompleteCount,
    ReturningForAction,
    ToSortedAssociative,
    TryCache,
    Wait
};
use League\Pipeline\Pipeline;

class Client
{
    public $url;
    private $email;
    private $password;
    private $sessionId;
    private $cookies;
    private $cache;
    private $log;

    private $guzzleClient;
    private function http()
    {
        if ($this->guzzleClient) {
            return $this->guzzleClient;
        }

        $this->guzzleClient = new GuzzleClient([
            'cookies' => $this->cookies,
            'base_uri' => $this->url,
        ]);
        $response = $this->guzzleClient->post('ctl/Core/AdminLoginAjax', [
            'form_params' => [
                'email' => $this->email,
                'password' => $this->password,
                'nossl' => 0,
                'redirect' => '',
            ]
        ]);
        return $this->guzzleClient;
    }

    public function get(string $url, array $params = null)
    {
        return $this->http()->get($url, $params);
    }

    public function post(string $url, array $params = null, string $type = null)
    {
        switch ($type) {
            case 'form':
                return $this->http()->post($url, [
                    'form_params' => $params
                ]);
            case 'json':
            default:
                return $this->http()->post($url, $params);
        }
    }

    private $searchSessionKey;
    public function searchSession(): string
    {
        if ($this->searchSessionKey) {
            return $this->searchSessionKey;
        }

        $result = preg_match(
            '/(?:'.preg_quote('BSD.cons_search.set_uid("').')(.+?)(?:\\")/',
            (string)$this->get('/admin/Constituent/ConsSearch')->getBody(),
            $match
        );

        return $this->searchSessionKey = $match[1];
    }

    public function undelete(int $consId)
    {
        if ($consId <= 0) {
            throw new InvalidArgumentException();
        }

        $response = $this->post(
            '/modules/constituent/admin/constituent_delete.php?undelete=1',
            [
                'cons_id' => $consId,
            ],
            'form'
        );
    }

    public function countUniqueSubscribers(int $id)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException();
        }

        $response = $this->get(
            '/modules/constituent/admin/group_list_ajax_replace.php',
            [
                'query' => [
                    'ajax_replace_id' => 'unique_emails_subscribed,'.$id
                ],
            ]
        );
        $response = simplexml_load_string((string)$response->getBody());
        return (int)str_replace(',', '', (string)$response->result);
    }

    public function activeRecurring()
    {
        $data = (new Pipeline)
            ->pipe(new ActiveRecurring($this, $this->log))
            ->pipe(new TryCache($this->cache, $this->log))
            ->pipe(function (array $parameters) : array {
                $preparedChunks = array_chunk($parameters['prepared'], 100, true);
                $result = [
                    'prepared' => [],
                    'ongoing' => $parameters['ongoing'],
                    'completed' => $parameters['completed'],
                    'done' => $parameters['done'],
                ];
                foreach ($preparedChunks as $prepared) {
                    $result = array_merge_recursive($result, (new Pipeline)
                        ->pipe(new BeginCount($this, $this->log))

                        /**
                         * ForceCompleteCount will try to fetch the count directly
                         * without waiting. Saves a roundtrip when doing many requests.
                         */
                        ->pipe(new ForceCompleteCount($this, $this->log))
                        ->pipe(new Wait($this, $this->log))
                        ->pipe(new CompleteCount($this, $this->log))
                        ->process([
                            'prepared' => $prepared,
                            'completed' => [],
                            'done' => [],
                            'ongoing' => [],
                        ]));
                }

                return $result;
            })
            ->process([]);
        return $data['done'][0]['data'];
    }

    public function returningForAction(
        DateTime $fromDate,
        DateTime $toDate = null,
        DateInterval $interval = null,
        DateInterval $windowSize = null
    ) {
        $fromDate = clone $fromDate;
        if ($toDate) {
            $toDate = clone $toDate;
        } else {
            $toDate = Carbon::now();
        }

        if (!$interval) {
            $interval = CarbonInterval::create(0, 1);
        }

        /**
         * Rturn empty array for impossible query
         */
        if ($fromDate->gt($toDate)) {
            return [];
        }

        return (new Pipeline)
            ->pipe(new ReturningForAction($this, $this->log))
            // ->pipe(new TryCache($this->cache, $this->log))
            ->pipe(function (array $parameters) : array {
                $preparedChunks = array_chunk($parameters['prepared'], 100, true);
                $result = [
                    'prepared' => [],
                    'ongoing' => $parameters['ongoing'],
                    'completed' => $parameters['completed'],
                    'done' => $parameters['done'],
                ];
                foreach ($preparedChunks as $prepared) {
                    $result = array_merge_recursive($result, (new Pipeline)
                        ->pipe(new BeginCount($this, $this->log))

                        /**
                         * ForceCompleteCount will try to fetch the count directly
                         * without waiting. Saves a roundtrip when doing many requests.
                         */
                        // ->pipe(new ForceCompleteCount($this, $this->log))
                        ->pipe(new Wait($this, $this->log))
                        ->pipe(new CompleteCount($this, $this->log))
                        ->process([
                            'prepared' => $prepared,
                            'completed' => [],
                            'done' => [],
                            'ongoing' => [],
                        ]));
                }

                return $result;
            })
            ->pipe(new ToSortedAssociative(
                function (string $key1, string $key2) : int {
                    $date1 = Carbon::createFromFormat('Y-m-d', $key1);
                    $date2 = Carbon::createFromFormat('Y-m-d', $key2);
                    return $date2->diffInSeconds($date1, false);
                }
            ))
            ->process([
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'interval' => $interval,
                'windowSize' => $windowSize,
            ]);
    }

    public function __construct(
        array $params,
        Repository $cache,
        Log $log
    ) {
        $this->email = $params['PRIVATE_API_EMAIL'];
        $this->password = $params['PRIVATE_API_PASSWORD'];
        $this->url = $params['PRIVATE_API_URL'];
        $this->cookies = new CookieJar;
        $this->cache = $cache;
        $this->log = $log;
    }
}
