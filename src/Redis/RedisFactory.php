<?php

namespace Be\F\Redis;

use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeException;


/**
 * Redis 工厂
 */
abstract class RedisFactory
{

    private static $cache = [];

    /**
     * 获取Redis对象
     *
     * @param string $name Redis名
     * @return \Be\F\Redis\Driver
     * @throws RuntimeException
     */
    public static function getInstance($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];
        self::$cache[$cid][$name] = self::newInstance($name);
        return self::$cache[$cid][$name];
    }

    /**
     * 新创建一个Redis对象
     *
     * @param string $redis Redis名
     * @return \Be\F\Redis\Driver
     * @throws RuntimeException
     */
    public static function newInstance($name = 'master')
    {
        $config = ConfigFactory::getInstance('System.Redis');
        if (!isset($config->$name)) {
            throw new RuntimeException('Redis配置项（' . $name . '）不存在！');
        }

        return new \Be\F\Redis\Driver($config->$name);
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
