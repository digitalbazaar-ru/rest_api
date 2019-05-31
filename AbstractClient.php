<?php

namespace DigitalBazaar\RestApi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

abstract class AbstractClient extends Client
{
    abstract protected function serviceUri();

    public function __construct(array $guzzleConfigs = [])
    {
        $guzzleConfigs['base_uri']        = $this->serviceUri();
        $guzzleConfigs['http_errors']     = false;
        $guzzleConfigs['allow_redirects'] = true;

        parent::__construct($guzzleConfigs);
    }

    public $lastRequestedData = [];

    protected function beforeRequest($method, $uri, &$options)
    {
        $this->lastRequestedData = [
            'method'  => $method,
            'uri'     => $uri,
            'options' => $options,
        ];

        return $this;
    }

    protected function afterRequest(Response $response)
    {
        return $this;
    }

    public function __call($method, $args)
    {
        if (in_array(str_replace('Async', '', $method), ['get', 'put', 'head', 'post', 'delete', 'patch'])) {

            $uri = $args[0];
            $options = isset($args[1]) ? $args[1] : [];

            $this->beforeRequest($method, $uri, $options);

            $args[0] = $uri;
            $args[1] = $options;

            $result = parent::__call($method, $args);
            $this->afterRequest($result);
            return $result;

        }

        return parent::__call($method, $args);
    }

    public function notNeedBodyLogForError($code)
    {
        return in_array($code, [401, 403, 404, 500, 504]);
    }

}


