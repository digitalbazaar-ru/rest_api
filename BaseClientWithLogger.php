<?php

namespace DigitalBazaar\RestApi;

use Monolog\Logger;
use Psr\Log\NullLogger;
use GuzzleHttp\Psr7\Response;

class BaseClientWithLogger extends BaseClient
{
    use ClientLogger;

    public function __construct($uri, array $guzzleConfigs = [], \Closure $closure = null, $loggerPath = '', $loggerName = null, $dayLog = 7, $logLevel = Logger::DEBUG, $filePermissions = 0755)
    {
        parent::__construct($uri, $guzzleConfigs, $closure);

        $this->logger = $this->initLogger($loggerPath, $loggerName, $dayLog, $logLevel, $filePermissions);
    }

    protected function beforeRequest($method, $uri, &$options)
    {
        $res = parent::beforeRequest($method, $uri, $options);

        $this->log('Запрос метод: ' . $method . ' адрес запроса: ' . rtrim($this->getConfig('base_uri'), '/') . '/' . ltrim($uri, '/'), [], Logger::INFO);
        $this->log('Параметры запроса: ', ['options' => $options], Logger::DEBUG);
        return $res;
    }

    protected function afterRequest(Response $response)
    {
        $success = ($response->getStatusCode() == 200);
        $this->log('Статус ответа сервиса: '  . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), [], $success ? Logger::INFO : Logger::ERROR);

        $content = $response->getBody()->getContents();
        $json = json_decode($content, 1);

        $body = is_null($json) ? $content : '';

        if ($this->notNeedBodyLogForError($response->getStatusCode())) {
            $body = $response->getReasonPhrase();
        }

        $this->log('Заголовки и тело ответа: ', ['json' => $json, 'body' => $body, 'header' => $response->getHeaders()], Logger::DEBUG);
        $response->getBody()->seek(0);

        return parent::afterRequest($response);
    }

    public function getLogger()
    {
        return $this->logger;
    }

    protected $loggerCallback = null;

    public function setLoggerCallback(\Closure $callback)
    {
        $this->loggerCallback = $callback;
    }

    public function log($message = '', $context = [], $level = Logger::DEBUG)
    {
        if (is_callable($this->loggerCallback)) {
            call_user_func_array($this->loggerCallback, [&$message, &$context, &$level]);
        }

        $this->getLogger()->log($level, $message, $context);
    }
}
