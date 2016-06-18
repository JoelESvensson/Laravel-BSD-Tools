<?php

namespace JoelESvensson\LaravelBsdTools\Api;

use Blue\Tools\Api\Client as SimpleBsdClient;
use function GuzzleHttp\json_decode;
use InvalidArgumentException;

class Client extends SimpleBsdClient
{
    /**
     * @var string The endpoint url. This is like the baseUrl but without
     * the appended api path
     */
    private $endpointUrl;

    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

    /**
     * @param string $id        Api user id
     * @param string $secret    Secret key
     * @param string $url       Endpoint url - e.g. https://kampanj.skiftet.org
     * @param array  $options   An associative array with optional configuration
     *  currently supported is: 'deferredResultMaxAttempts'
     *  'deferredResultInterval' and 'logger'
     */
    public function __construct($config = [], $options = [])
    {
        $mandatoryNames = ['ENDPOINT_URL', 'API_USER_ID', 'API_USER_SECRET'];
        foreach ($mandatoryNames as $name) {
            if (!isset($config[$name])) {
                throw new InvalidArgumentException(
                    "Mandatory value with name: $name was not found in the configuration"
                );
            }
        }
        parent::__construct(
            $config[$mandatoryNames[1]],
            $config[$mandatoryNames[2]],
            $config[$mandatoryNames[0]]
        );
        $this->endpointUrl = $config[$mandatoryNames[0]];
        $optionNames = [
            'deferredResultMaxAttempts',
            'deferredResultInterval',
            'logger'
        ];
        foreach ($optionNames as $optionName) {
            if (isset($option[$optionName])) {
                $this->{'set' . ucfirst($optionName)}($option[$optionName]);
            }
        }
    }

    /**
     * @param   int The Id of the signup
     * @return  int Number of signups in the signup
     */
    public function signupCount($signupId)
    {
        $signupId = (int)$signupId;
        return (int)file_get_contents(
            $this->getEndpointUrl() . '/utils/cons_counter/signup_counter.ajax.php?signup_form_id=' . $signupId
        );
    }

    /**
     * @param   string $slug Slug for the signup url
     * @return  string The full signup url
     */
    public function signupUrlBySlug($slug)
    {
        return $this->getEndpointUrl() . '/page/s/' . $slug;
    }

    public function get(
        string $path,
        array $parameters = null,
        string $contentType = null
    ) {
        if ($contentType) {
            switch ($contentType) {
                case 'xml':
                case 'json':
                    break;
                default:
                    throw new InvalidArgumentException(
                        "contentType '$contentType' is invalid"
                    );
            }
        } else {
            $contentType = 'xml';
        }

        $response = parent::get($path, $parameters);
        switch ($contentType) {
            case 'xml':
                return (string)$response->getBody();
            case 'json':
                return json_decode((string)$response->getBody(), true);
        }
    }

    public function post(
        string $path,
        array $parameters = null,
        array $data = null,
        string $contentType = null
    ) {
        if ($contentType) {
            switch ($contentType) {
                case 'xml':
                case 'json':
                    break;
                default:
                    throw new InvalidArgumentException(
                        "contentType '$contentType' is invalid"
                    );
            }
        } else {
            $contentType = 'xml';
        }

        $response = parent::post($path, $parameters, $data);
        switch ($contentType) {
            case 'xml':
                return (string)$response->getBody();
            case 'json':
                return json_decode((string)$response->getBody(), true);
        }
    }
}
