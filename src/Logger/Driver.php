<?php

namespace Be\Framework\Logger;

use Monolog\Logger;
use Be\Framework\Logger\Handler\FileHandler;
use Be\Framework\Logger\Processor\FileProcessor;
use Be\Framework\Be;

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

            $configSystemLog = Be::getConfig('System.Log');

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
                echo '不支持的系统日志方法：' . $name . '！';
                exit;
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
