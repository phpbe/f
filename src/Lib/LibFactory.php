<?php

namespace Be\F\Lib;

use Be\F\Runtime\RuntimeException;

/**
 * Lib 工厂
 */
abstract class LibFactory
{

    private static $cache = [];

    /**
     * 获取指定的库（单例）
     *
     * @param string $name 库名，可指定命名空间，调用第三方库
     * @return mixed
     * @throws RuntimeException
     */
    public static function getInstance($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];
        self::$cache[$cid][$name] = self::newInstance($name);
        return self::$cache[$cid][$name];
    }

    /**
     * 新创建一个指定的库
     *
     * @param string $name 库名，可指定命名空间，调用第三方库
     * @return mixed
     * @throws RuntimeException
     */
    public static function newInstance($name)
    {
        $class = null;
        if (strpos($name, '\\') === false) {
            $class = 'Be\\F\\Lib\\' . $name . '\\' . $name;
        } else {
            $class = $name;
        }
        if (!class_exists($class)) throw new RuntimeException('Lib ' . $class . ' doesn\'t exist!');

        return new $class();
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
