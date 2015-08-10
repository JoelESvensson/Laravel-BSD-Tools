<?php

namespace Midvinter\BSDTools;

use Blue\Tools\Api\Client as BSDToolsClient;
use InvalidArgumentException;
use ReflectionObject;

class BSDTools extends BSDToolsClient {

    /**
     * @param string $id        Api user id
     * @param string $secret    Secret key
     * @param string $url       Endpoint url - e.g. http://kampanj.skiftet.org
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
                    'Mandatory value with name: "' . $name . '" was not found in the configuration'
                );
            }
        }
        parent::__construct(
            $config[$mandatoryNames[1]],
            $config[$mandatoryNames[2]],
            $config[$mandatoryNames[0]]
        );
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
     * @return string The endpoint url
     */
    public function getBaseUrl() {
        $reflection = new ReflectionObject($this);
        $reflection = $reflection->getParentClass()->getProperty('baseUrl');
        $reflection->setAccessible(true);
        $baseUrl = $this->baseUrl;
        $reflection->setAccessible(false);
        return $baseUrl;
    }

    /**
     * @param   int The Id of the signup
     * @return  int Number of signups in the signup
     */
    public function signupCount($signupId) {
        $signupId = (int)$signupId;
        return (int)file_get_contents($this->getBaseUrl() . '/utils/cons_counter/signup_counter.ajax.php?signup_form_id=' . $signupId);
    }

}
