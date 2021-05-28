<?php

namespace Be\F\MongoDB;

use Be\F\Config\ConfigFactory;
use Be\F\Gc;
use Be\F\Runtime\RuntimeException;

/**
 * MongoDB 工厂
 */
abstract class MongoDBFactory
{

    private static $cache = [];

    /**
     * 获取MongoDB对象（单例）
     *
     * @param string $name MongoDB名
     * @return \Be\F\MongoDB\Driver
     * @throws RuntimeException
     */
    public static function getInstance($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];
        self::$cache[$cid][$name] = self::newInstance($name);
        Gc::register($cid, self::class);
        return self::$cache[$cid][$name];
    }

    /**
     * 新创建一个MongoDB对象
     *
     * @param string $mongoDB MongoDB名
     * @return \Be\F\MongoDB\Driver
     * @throws RuntimeException
     */
    public static function newInstance($name = 'master')
    {
        $config = ConfigFactory::getInstance('System.MongoDB');
        if (!isset($config->$name)) {
            throw new RuntimeException('MongoDB config item (' . $name . ') doesn\'t exist!');
        }
        return new \Be\F\MongoDB\Driver($config->$name);
    }

    /**
     * 回收资源
     */
    public static function recycle()
    {
        $cid = \Swoole\Coroutine::getuid();
        unset(self::$cache[$cid]);
    }

}
