<?php

namespace Be\F\Log;


use Be\F\Gc;

/**
 * Logger 工厂
 */
abstract class LogFactory
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
            Gc::register($cid, self::class);
        }
        return self::$cache[$cid];
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
