<?php

namespace Be\F\Db;

use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeFactory;

/**
 * Table 工厂
 */
abstract class TableFactory
{

    private static $cache = [];

    /**
     * 获取指定的一个数据库表对象（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\F\Db\Table
     */
    public static function getInstance($name, $db = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$db][$name])) return self::$cache[$cid][$db][$name];
        self::$cache[$cid][$db][$name] = self::newInstance($name, $db);
        return self::$cache[$cid][$db][$name];
    }

    /**
     * 新创建一个数据库表对象
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\F\Db\Table
     */
    public static function newInstance($name, $db = 'master')
    {
        $runtime = RuntimeFactory::getInstance();
        $frameworkName = $runtime->getFrameworkName();

        $path = $runtime->getCachePath() . '/Table/' . $db . '/' . $name . '.php';
        $configSystem = ConfigFactory::getInstance('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            DbHelper::updateTable($name, $db);
            include_once $path;
        }

        $class = 'Be\\' . $frameworkName . '\\Cache\\Table\\' . $db . '\\' . $name;
        return (new $class());
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
