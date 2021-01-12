<?php

namespace Be\F\Response;


/**
 * Response 工厂
 */
abstract class ResponseFactory
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
        if (isset(self::$cache[$cid])) {
            return self::$cache[$cid];
        }
        return null;
    }

    /**
     * 设置Runtime实例
     *
     * @param Driver $instance
     */
    public static function setInstance($instance)
    {
        $cid = \Swoole\Coroutine::getuid();
        self::$cache[$cid] = $instance;
    }


}
