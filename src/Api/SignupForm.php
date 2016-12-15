<?php
declare(strict_types=1);

namespace JoelESvensson\LaravelBsdTools\Api;

use JoelESvensson\LaravelBsdTools\Api\Client as ApiClient;
use DOMDocument;

class SignupForm
{

    /**
     * @var ApiClient
     */
    private $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    private function byId(int $id)
    {
        return $this->api->get('signup/get_form', ['signup_form_id' => $id]);
    }

    public function get($id, array $options = null)
    {
        if (is_string($id)) {
            $id = (int)$id;
        } elseif (!is_int($id)) {
            throw new InvalidArgumentException();
        }

        return $this->byId($id);
    }

    public function process($id, $fieldData)
    {
        if (is_string($id)) {
            $id = (int)$id;
        } elseif (!is_int($id)) {
            throw new InvalidArgumentException();
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $api = $dom->createElement('api');
        $signupForm = $dom->createElement('signup_form');
        $signupForm->setAttribute('id', $id);
        foreach ($fieldData as $key => $value) {
            $field = $dom->createElement('signup_form_field', $value);
            $field->setAttribute('id', $key);
            $signupForm->appendChild($field);
        }

        $api->appendChild($signupForm);
        $dom->appendChild($api);
        return $this->api->post('signup/process_signup', [], $dom->saveXML());
    }

    /**
     * @param array|string $params
     * @param ?string $reason
     */
    public function list()
    {
        return $this->api->get('signup/list_forms');
    }

    public function fastCount(int $id)
    {
        return $this->api->signupCount($id);
    }
}
