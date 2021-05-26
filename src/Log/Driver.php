<?php

namespace Be\F\Log;

use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeException;
use Monolog\Logger;
use Be\F\Log\Handler\FileHandler;
use Be\F\Log\Processor\FileProcessor;

/**
 * 日志类
 *
 * @method static bool debug(\Throwable $t)
 * @method static bool info(\Throwable $t)
 * @method static bool notice(\Throwable $t)
 * @method static bool warning(\Throwable $t)
 * @method static bool error(\Throwable $t)
 * @method static bool critical(\Throwable $t)
 * @method static bool alert(\Throwable $t)
 * @method static bool emergency(\Throwable $t)
 */
class Driver
{

    private $logger = null;


    /**
     *
     * @return Logger
     */
    private function getLogger()
    {
        if ($this->logger === null) {

            $configSystemLog = ConfigFactory::getInstance('System.Log');

            $level = Logger::DEBUG;
            if (isset($configSystemLog->level)) {
                switch ($configSystemLog->level) {
                    case 'debug':
                        $level = Logger::DEBUG;
                        break;
                    case 'info':
                        $level = Logger::INFO;
                        break;
                    case 'notice':
                        $level = Logger::NOTICE;
                        break;
                    case 'warning':
                        $level = Logger::WARNING;
                        break;
                    case 'error':
                        $level = Logger::ERROR;
                        break;
                    case 'critical':
                        $level = Logger::CRITICAL;
                        break;
                    case 'alert':
                        $level = Logger::ALERT;
                        break;
                    case 'emergency':
                        $level = Logger::EMERGENCY;
                        break;
                }
            }

            $logger = new Logger('Be');

            $handler = new FileHandler($level);
            $logger->pushHandler($handler);

            $processor = new FileProcessor($level, $configSystemLog);
            $logger->pushProcessor($processor);

            $this->logger = $logger;
        }

        return $this->logger;
    }

    /**
     *
     * @param $name
     * @param $arguments
     * @return string
     * @throws RuntimeException
     */
    public function __call($name, $arguments)
    {
        $level = null;
        switch ($name) {
            case 'debug':
                $level = Logger::DEBUG;
                break;
            case 'info':
                $level = Logger::INFO;
                break;
            case 'notice':
                $level = Logger::NOTICE;
                break;
            case 'warning':
                $level = Logger::WARNING;
                break;
            case 'error':
                $level = Logger::ERROR;
                break;
            case 'critical':
                $level = Logger::CRITICAL;
                break;
            case 'alert':
                $level = Logger::ALERT;
                break;
            case 'emergency':
                $level = Logger::EMERGENCY;
                break;
            default:
                // 不支持的系统日志方法
                throw new RuntimeException('not support method ' . $name . '!');
        }

        /**
         * @var \Throwable $t
         */
        $t = $arguments[0];

        $message = $t->getMessage();
        $context = [
            'file' => $t->getFile(),
            'line' => $t->getLine(),
            'code' => $t->getCode(),
            'trace' => $t->getTrace(),
        ];

        return $this->getLogger()->addRecord($level, $message, $context);
    }


}
