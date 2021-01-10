<?php

namespace Be\Framework\App;


/**
 * Service 工厂
 */
abstract class ServiceFactory
{

    private static $cache = [];

    /**
     * 获取指定的一个服务（单例）
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function getInstance($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];
        self::$cache[$cid][$name] = self::newInstance($name);
        return self::$cache[$cid][$name];
    }

    /**
     * 新创建一个服务
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function newInstance($name)
    {
        $parts = explode('.', $name);
        $app = array_shift($parts);
        $class = 'Be\\App\\' . $app . '\\Service\\' . implode('\\', $parts);
        return new $class();
    }

}
