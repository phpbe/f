<?php

namespace Be\F\Db;

use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeFactory;

/**
 * TableProperty 工厂
 */
abstract class TablePropertyFactory
{

    private static $cache = [];

    /**
     * 获取指定的一个数据库表对象（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\F\Db\TableProperty
     */
    public static function getInstance($name, $db = 'master')
    {
        if (isset(self::$cache[$db][$name])) return self::$cache[$db][$name];

        $runtime = RuntimeFactory::getInstance();
        $frameworkName = $runtime->getFrameworkName();

        $path = $runtime->getCachePath() . '/TableProperty/' . $db . '/' . $name . '.php';
        $configSystem = ConfigFactory::getInstance('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            DbHelper::updateTableProperty($name, $db);
            include_once $path;
        }

        $class = 'Be\\' . $frameworkName . '\\Cache\\TableProperty\\' . $db . '\\' . $name;
        self::$cache[$db][$name] = new $class();
        return self::$cache[$db][$name];
    }


}
