<?php

namespace Press\Engine\Logger;

use Press\Engine\AppHandler;
use Press\Engine\Storage\FileSystem\FileSystemWriter;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use DateTime;

/**
 * Class Logger
 * @package Press\Engine\Logger
 */
final class Logger extends AbstractLogger
{
    const PATH = 'var/log' ;

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array()): void
    {
        $now = new DateTime('now');

        $message = $this->interpolate($message, $context);
        $fileSystemWriter = new FileSystemWriter( $this->getFilePath($level) );
        $fileSystemWriter->writeln("{$now->format('Y-m-d\TH:i:s')} {$level} -> \"{$message}\"");
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    private function interpolate(string $message, array $context = []): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * @param string $level
     * @return string
     */
    private function getFilePath( string $level ): string {
        $path = AppHandler::getProjectPatch() . '/' . self::PATH;

        switch ($level){
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
            //    return "{$path}/exceptions.log";
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
            case LogLevel::DEBUG:
                return "{$path}/exceptions.log";
            case LogLevel::INFO:
                return "{$path}/info.log";
        }

        return "{$path}/{$level}.log";
    }
}
