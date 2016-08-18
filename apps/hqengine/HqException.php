<?php

namespace HqEngine;

use Phalcon\DI;

class HqException extends \Phalcon\Exception {

    public function __construct($message = "", $args = [], $code = 0, \Exception $previous = null)
    {
        parent::__construct(vsprintf($message, $args), $code, $previous);
    }

    public static function logException(\Exception $e)
    {
        return self::logError(
                        'Exception', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
        );
    }

    public static function logError($type, $message, $file, $line, $trace = null)
    {
        $id         = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
        $di         = DI::getDefault();
        $template   = "<%s> [%s] %s (File: %s Line: [%s])";
        $logMessage = sprintf($template, $id, $type, $message, $file, $line);

        if ($di->has('profiler'))
        {
            $profiler = $di->get('profiler');
            if ($profiler)
            {
                $profiler->addError($logMessage, $trace);
            }
        }

        if ($trace)
        {
            $logMessage .= $trace . PHP_EOL;
        }
        else
        {
            $logMessage .= PHP_EOL;
        }

        if ($di->has('logger'))
        {
            $logger = $di->get('logger');
            if ($logger)
            {
                $logger->error($logMessage);
            }
            else
            {
                throw new Exception($logMessage);
            }
        }
        else
        {
            throw new Exception($logMessage);
        }

        return $id;
    }

}
