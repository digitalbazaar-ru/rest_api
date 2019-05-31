<?php

namespace DigitalBazaar\RestApi;

use Throwable;

class ServiceException extends \Exception
{

    public $cleanError = '';

    public function __construct($cleanError, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->cleanError = $cleanError;

        parent::__construct($message, $code, $previous);
    }

    public function cleanError()
    {
        return $this->cleanError;
    }

}
