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
use JoelESvensson\LaravelBsdTools\PrivateApi\Stages\{ //@codingStandardsIgnoreLine
    ReturningForAction,
    Wait,
    CompleteCount,
    Cache,
    TryCache,
    BeginCount
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

    public function post(string $url, array $params = null)
    {
        return $this->http()->post($url, $params);
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

    public function returningForAction(
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

        return (new Pipeline)
            ->pipe(new ReturningForAction($this))
            ->pipe(new TryCache($this->cache))
            ->pipe(new BeginCount($this))
            ->pipe(new Wait($this))
            ->pipe(new CompleteCount($this))
            ->pipe(new Cache($this->cache))
            ->process([
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'interval' => $interval,
            ]);
    }

    public function __construct(array $params, Repository $cache)
    {
        $this->email = $params['PRIVATE_API_EMAIL'];
        $this->password = $params['PRIVATE_API_PASSWORD'];
        $this->url = $params['PRIVATE_API_URL'];
        $this->cookies = new CookieJar;
        $this->cache = $cache;
    }
}
