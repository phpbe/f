<?php

namespace Be\F\Es;

use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeException;
use Elasticsearch\ClientBuilder;


/**
 * ES 工厂
 */
abstract class EsFactory
{

    private static $cache = [];


    /**
     * 获取ES对象
     *
     * @return \Elasticsearch\Client
     * @throws RuntimeException
     */
    public static function getInstance()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid])) return self::$cache[$cid];
        self::$cache[$cid] = self::newInstance();
        return self::$cache[$cid];
    }

    /**
     * 新创建一个ES对象
     *
     * @return \Elasticsearch\Client
     * @throws RuntimeException
     */
    public static function newInstance()
    {
        $config = ConfigFactory::getInstance('System.Es');
        $driver = ClientBuilder::create()->setHosts($config->hosts)->build();
        return $driver;
    }

    /**
     * 回收资源
     */
    public static function release()
    {
        $cid = \Swoole\Coroutine::getuid();
        unset(self::$cache[$cid]);
    }

}
