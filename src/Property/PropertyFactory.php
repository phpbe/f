<?php

namespace Be\F\Property;

use Be\F\Runtime\RuntimeException;


/**
 * Property 工厂
 */
abstract class PropertyFactory
{

    private static $cache = [];

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return Driver
     * @throws RuntimeException
     */
    public static function getInstance($name)
    {
        if (isset(self::$cache[$name])) return self::$cache[$name];

        $parts = explode('.', $name);
        $class = 'Be\\' . implode('\\', $parts) . '\\Property';
        if (!class_exists($class)) throw new RuntimeException('属性 ' . $name . ' 不存在！');
        $instance = new $class();

        self::$cache[$name] = $instance;
        return self::$cache[$name];
    }

}
