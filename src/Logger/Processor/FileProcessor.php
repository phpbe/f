<?php

namespace Be\F\Logger\Processor;

use Be\F\Util\FileSystem\FileSize;
use Monolog\Logger;

class FileProcessor
{

    private $level;

    private $config;

    /**
     * SystemProcessor constructor.
     * @param int $level 默认处理的最低日志级别，低于该级别不处理
     * @param Mixed $config 系统应用中的日志配置项
     */
    public function __construct($level = Logger::DEBUG, $config)
    {
        $this->level = $level;
        $this->config = $config;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $config = $this->config;

        if ($record['level'] < $this->level) {
            return $record;
        }

        $hash = md5(json_encode([
            'file' => $record['context']['file'],
            'line' => $record['context']['line'],
            'message' => $record['message']
        ]));

        $record['extra']['hash'] = $hash;

        if (isset($config->get) && $config->get) {
            $record['extra']['get'] = &$_GET;
        }

        if (isset($config->post) && $config->post) {
            $record['extra']['post'] = &$_POST;
        }

        if (isset($config->request) && $config->request) {
            $record['extra']['request'] = &$_REQUEST;
        }

        if (isset($config->cookie) && $config->cookie) {
            $record['extra']['cookie'] = &$_COOKIE;
        }

        if (isset($config->session) && $config->session) {
            $record['extra']['session'] = &$_SESSION;
        }

        if (isset($config->server) && $config->server) {
            $record['extra']['server'] = &$_SERVER;
        }

        if (isset($config->memery) && $config->memery) {
            $bytes = memory_get_usage();
            $record['extra']['memory_usage'] = FileSize::int2String($bytes);

            $bytes = memory_get_peak_usage();
            $record['extra']['memory_peak_usage'] = FileSize::int2String($bytes);
        }

        return $record;
    }

}