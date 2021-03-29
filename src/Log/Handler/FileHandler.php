<?php

namespace Be\F\Log\Handler;

use Be\F\Runtime\RuntimeFactory;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class FileHandler extends AbstractProcessingHandler
{


    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }


    // 日志存储实现
    protected function write(array $record)
    {
        $t = time();

        $year = date('Y', $t);
        $month = date('m', $t);
        $day = date('d', $t);

        $dir = RuntimeFactory::getInstance()->getDataPath() . '/System/Log/' .  $year . '/' . $month . '/' . $day . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            chmod($dir, 0755);
        }

        $logFileName = $record['extra']['hash'];

        $logFilePath = $dir . $logFileName;

        if (!file_exists($logFilePath)) {
            $record['extra']['record_time'] = $t;
            file_put_contents($logFilePath, json_encode($record));
        }

        $indexFilePath = $dir . 'index';
        $f = fopen($indexFilePath, 'ab+');
        if ($f) {
            fwrite($f, pack('H*', $logFileName));
            fwrite($f, pack('L', $t));
            fclose($f);
        }

        return true;
    }

}