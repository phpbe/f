<?php

namespace Be\F\Log\Processor;

use Be\F\Request\RequestFactory;
use Be\F\Runtime\RuntimeFactory;
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

        $request = RequestFactory::getInstance();
        if (isset($config->get) && $config->get) {
            $record['extra']['get'] = $request->get();
        }

        if (isset($config->post) && $config->post) {
            $record['extra']['post'] = $request->post();
        }

        if (isset($config->request) && $config->request) {
            $record['extra']['request'] = $request->request();
        }

        if (isset($config->cookie) && $config->cookie) {
            $record['extra']['cookie'] = $request->cookie();
        }

        if (RuntimeFactory::getInstance()->getFrameworkName() != 'Sf') {
            if (isset($config->session) && $config->session) {
                $session = \Be\F\Session\SessionFactory::getInstance();
                $record['extra']['session'] = $session->get();
            }
        }

        if (isset($config->server) && $config->server) {
            $record['extra']['server'] = $request->server();
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