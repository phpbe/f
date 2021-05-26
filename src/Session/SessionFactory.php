<?php

namespace Be\F\Session;

use Be\F\Config\ConfigFactory;


/**
 * Session 工厂
 */
abstract class SessionFactory
{

    private static $cache = [];

    /**
     * 获取SESSION
     *
     * @return Driver
     * @throws SessionException
     */
    public static function getInstance()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (!isset(self::$cache[$cid])) {
            $config = ConfigFactory::getInstance('System.Session');
            $driver = '\\Be\\F\\Session\\Driver\\' . $config->driver;
            if (!class_exists($driver)) {
                throw new SessionException('Session driver' . $config->driver . ' doesn\'t exist!');
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
            $session = self::$cache[$cid];
            $session->close();
            unset(self::$cache[$cid]);
        }
    }

}
