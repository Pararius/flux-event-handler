<?php
declare(strict_types=1);

namespace TreeHouse\Log;

use Psr\Log\LoggerInterface;

class IoLogger implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                $output = STDERR;
                break;
            default:
                $output = STDOUT;
        }

        $log = [$message];

        if (!empty($context)) {
            $log[] = serialize($context);
        }

        fwrite($output, implode(PHP_EOL, $log) . PHP_EOL);
    }
}
