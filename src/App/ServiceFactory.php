<?php

namespace Be\F\App;

use Be\F\Runtime\RuntimeFactory;


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
        $class = 'Be\\' . RuntimeFactory::getInstance()->getFrameworkName() . '\\App\\' . $app . '\\Service\\' . implode('\\', $parts);
        return new $class();
    }

    /**
     * 回收指定资源
     *
     * @param string $key 为null时回收当前协程的所有私有资源
     */
    public static function recycle($key = null)
    {
        $cid = \Swoole\Coroutine::getuid();
        if ($key === null) {
            unset(self::$cache[$cid]);
        } else {
            unset(self::$cache[$cid][$key]);
        }
    }

}
