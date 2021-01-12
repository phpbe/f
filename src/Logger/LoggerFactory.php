<?php

namespace Be\F\Logger;


/**
 * Logger 工厂
 */
abstract class LoggerFactory
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
