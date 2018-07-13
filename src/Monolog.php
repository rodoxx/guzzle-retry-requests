<?php

namespace GuzzleRetry;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Monolog
{
    public function getLogger()
    {
        $logger = new Logger('log');
        $filename = ('./logs/log.log');

        $formatter = new LineFormatter(null, null, false, true);

        $stream = new StreamHandler($filename, Logger::DEBUG);
        $stream->setFormatter($formatter);
        $logger->pushHandler($stream);

        return $logger;
    }

}