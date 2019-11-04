<?php
declare(strict_types=1);

namespace TreeHouse\Log;

use Psr\Log\LoggerInterface;

class StdoutLogger implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        echo $message;
    }

    public function alert($message, array $context = array())
    {
        echo $message;
    }

    public function critical($message, array $context = array())
    {
        echo $message;
    }

    public function error($message, array $context = array())
    {
        echo $message;
    }

    public function warning($message, array $context = array())
    {
        echo $message;
    }

    public function notice($message, array $context = array())
    {
        echo $message;
    }

    public function info($message, array $context = array())
    {
        echo $message;
    }

    public function debug($message, array $context = array())
    {
        echo $message;
    }

    public function log($level, $message, array $context = array())
    {
        echo $message;
    }
}
