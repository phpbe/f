<?php

namespace Be\F\Config;

use Be\F\Runtime\RuntimeException;
use Be\F\Runtime\RuntimeFactory;

/**
 * Config工厂
 */
abstract class ConfigFactory
{

    public static $cache = []; // 缓存资源实例

    /**
     * 获取指定的配置文件
     *
     * @param string $name 配置文件名
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
     * 新创建一个指定的配置文件
     *
     * @param string $name 配置文件名
     * @return mixed
     * @throws RuntimeException
     */
    public static function newInstance($name)
    {
        $parts = explode('.', $name);
        $appName = $parts[0];
        $configName = $parts[1];

        $frameworkName = RuntimeFactory::getInstance()->getFrameworkName();

        $class = 'Be\\' . $frameworkName . '\\Data\\' . $appName . '\\Config\\' . $configName;
        if (class_exists($class)) {
            return new $class();
        }

        $class = 'Be\\' . $frameworkName . '\\App\\' . $appName . '\\Config\\' . $configName;
        if (class_exists($class)) {
            return new $class();
        }

        throw new RuntimeException('配置文件 ' . $name . ' 不存在！');
    }

}
