<?php

namespace Be\Framework\Cache;


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
     */
    public static function getInstance()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (!isset(self::$cache[$cid])) {
            self::$cache[$cid] = new Driver();
        }
        return self::$cache[$cid];
    }

}
