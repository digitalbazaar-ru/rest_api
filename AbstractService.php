<?php

namespace DigitalBazaar\RestApi;

use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractService
{
    /** @var AbstractClient */
    protected $client;

    public function __construct(AbstractClient $client = null)
    {
        $this->client = ! is_null($client) ? $client : $this->getDefaultServiceClient();
    }

    abstract protected function getDefaultServiceClient();

    /**
     * @param ResponseInterface $response
     * @param bool $answerIsJson
     * @return mixed|string
     * @throws ServiceException
     */
    protected function getResponseData(ResponseInterface $response, $answerIsJson = false)
    {
        $content = $this->parseContent($response->getBody()->getContents());

        $isJson = $this->wantsJson($response) || $answerIsJson;

        if ($isJson) {

            $content = json_decode($content, 1);

            if (array_has($content, 'custom_api')) {
                array_set($content, 'custom_api', json_decode(array_get($content, 'custom_api', ''), 1));
            }
        }

        if ($this->isErrorResponse($response)) {

            $cleanError =  $isJson
                ? array_get($content, 'error', 'Undefined Error')
                : $content
            ;

            $errorMessage = $cleanError . (
                $isJson
                    ? ' (' . array_get($content, 'error_code', 0) . ') : '. array_get($content, 'error_description', '')
                    : ''
                )
            ;

            $paramsForMessage = $this->client->lastRequestedData;

            if (array_has($paramsForMessage, 'options.headers.Authorization')) {
                array_set($paramsForMessage,'options.headers.Authorization', '*** Secret token ***');
            }

            $errorMessage .= ' Http Error: ' . $response->getReasonPhrase() . ', With params: ' . json_encode($paramsForMessage);

            throw new ServiceException($cleanError, $errorMessage, $response->getStatusCode());
        }

        return $content;
    }

    protected function parseContent($content)
    {
        return $content;
    }

    protected function isErrorResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() < 200 || $response->getStatusCode() >= 300;
    }

    protected function wantsJson(ResponseInterface $response)
    {
        return strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false;
    }


    protected static function getLogFileName($baseNameSpace = __NAMESPACE__)
    {
        return Str::snake(trim(str_replace([$baseNameSpace, '\\'], ['', '_'], static::class), '_')) . '.log';
    }

}
