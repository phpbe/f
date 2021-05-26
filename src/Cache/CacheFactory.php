<?php

namespace Be\F\Cache;

use Be\F\Config\ConfigFactory;

/**
 * Cache 工厂
 */
abstract class CacheFactory
{

    private static $cache = [];

    /**
     * 获取Runtime实例
     *
     * @return Driver
     * @throws CacheException
     */
    public static function getInstance()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (!isset(self::$cache[$cid])) {
            $config = ConfigFactory::getInstance('System.Cache');
            $driver = '\\Be\\F\\Cache\\Driver\\' . $config->driver;
            if (!class_exists($driver)) {
                throw new CacheException('Cache driver' . $config->driver . ' doesn\'t exist!');
            }

            self::$cache[$cid] = new $driver($config);
        }
        return self::$cache[$cid];
    }

    /**
     * 回收资源
     */
    public static function release()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid])) {
            $driver = self::$cache[$cid];
            $driver->close();

            unset(self::$cache[$cid]);
        }
    }

}
