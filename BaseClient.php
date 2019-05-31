<?php

namespace DigitalBazaar\RestApi;


class BaseClient extends AbstractClient
{
    protected $endpoint = '';

    /** @var \Closure */
    protected $onBeforeCallback;

    public function __construct($uri, array $guzzleConfigs = [], \Closure $closure = null)
    {
        $this->endpoint = $uri;

        if (! is_null($closure)) {
            $this->onBeforeRequestCallback($closure);
        }

        $guzzleConfigs['base_uri']        = $this->serviceUri();
        $guzzleConfigs['http_errors']     = false;
        $guzzleConfigs['allow_redirects'] = true;

        parent::__construct($guzzleConfigs);
    }

    public function setServiceUri($url)
    {
        $this->endpoint = $url;
        return $this;
    }

    protected function serviceUri()
    {
        return $this->endpoint;
    }

    public function onBeforeRequestCallback(\Closure $closure)
    {
        $this->onBeforeCallback = $closure;
        return $this;
    }

    protected function beforeRequest($method, $uri, &$options)
    {
        if (is_callable($this->onBeforeCallback)) {
            call_user_func_array($this->onBeforeCallback, [$method, $uri, &$options]);
        }

        return parent::beforeRequest($method, $uri, $options);
    }
}
