<?php

namespace DigitalBazaar\RestApi;

use Monolog\Logger;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait ClientLogger
{
    /** @var Logger */
    private $logger;

    protected function initLogger($loggerPathFile, $name = null, $daysLog = 7, $loggerLevel = Logger::DEBUG, $filePermissions = 0755)
    {

        if (empty($loggerPathFile)) {
            $this->logger = new NullLogger;

            return $this->logger;
        }


        $this->logger = new Logger($name ?: $this->getLogName());

        $this->logger->pushHandler(
            $handler = new \Monolog\Handler\RotatingFileHandler($loggerPathFile, $daysLog, $loggerLevel, true, $filePermissions)
        );

        return $this->logger;
    }

    protected static function getLogName()
    {
        return static::class;
    }

    protected static function getLogFileName($baseNameSpace = __NAMESPACE__)
    {
        return Str::snake(trim(str_replace([$baseNameSpace, '\\'], ['', '_'], static::class), '_')) . '.log';
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function getLogger()
    {
        return $this->logger;
    }
}


