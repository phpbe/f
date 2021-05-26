<?php

namespace Be\F\Config;

use Be\F\Runtime\RuntimeException;
use Be\F\Runtime\RuntimeFactory;

/**
 * Config工厂
 */
abstract class ConfigFactory
{

    private static $cache = []; // 缓存资源实例

    /**
     * 获取指定的配置文件
     *
     * @param string $name 名称
     * @return mixed
     * @throws RuntimeException
     */
    public static function getInstance($name)
    {
        if (isset(self::$cache[$name])) return self::$cache[$name];
        self::$cache[$name] = self::newInstance($name);
        return self::$cache[$name];
    }

    /**
     * 新创建一个指定的配置文件
     *
     * @param string $name 名称
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
            $instance =  new $class();
            // ConfigHelper::update($name, $instance);
            return $instance;
        }

        throw new RuntimeException('Config ' . $name . ' doesn\t exist!');
    }

}
