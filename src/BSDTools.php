<?php

namespace Midvinter\BSDTools;

use Blue\Tools\Api\Client as BSDToolsClient;
use InvalidArgumentException;

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

}
