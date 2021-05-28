<?php

namespace Be\F\Db;

use Be\F\Config\ConfigFactory;
use Be\F\Gc;

/**
 * Db 工厂
 */
abstract class DbFactory
{

    private static $cache = [];

    private static $pools = null;

    public static function init()
    {
        if (self::$pools === null) {
            self::$pools = [];
            $config = ConfigFactory::getInstance('System.Db');
            foreach ($config as $k => $v) {
                if ($v['driver'] != 'mysql') continue;

                $size = isset($v['pool']) ? intval($v['pool']) : 0;
                if ($size <= 0) {
                    continue;
                }

                $options = array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                );

                $port = isset($v['port']) ? $v['port'] : 3306;
                $charset = isset($v['charset']) ? $v['charset'] : 'utf8mb4';

                $pdoConfig = new \Swoole\Database\PDOConfig();
                $pdoConfig->withHost($v['host'])
                    ->withPort($port)
                    ->withDbName($v['name'])
                    ->withUsername($v['username'])
                    ->withPassword($v['password'])
                    ->withCharset($charset)
                    ->withOptions($options);

                self::$pools[$k] = new \Swoole\Database\PDOPool($pdoConfig, $size);
            }
        }
    }

    /**
     * 获取数据库对象（单例）
     *
     * @param string $name 数据库名
     * @return \Be\F\Db\Driver
     * @throws DbException
     */
    public static function getInstance($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];

        $config = ConfigFactory::getInstance('System.Db');
        if (!isset($config->$name)) {
            throw new DbException('数据库配置项（' . $name . '）不存在！');
        }
        $configData = $config->$name;

        $driver = null;
        switch ($configData['driver']) {
            case 'mysql':
                if (isset(self::$pools[$name])) {
                    $pool = self::$pools[$name];
                    $pdo = $pool->get();
                    $driver = new \Be\F\Db\Driver\Mysql($name, $pdo);
                } else {
                    $driver = new \Be\F\Db\Driver\Mysql($name);
                }
                break;
            case 'Mssql':
                $driver = new \Be\F\Db\Driver\Mssql($name);
                break;
            case 'Oracle':
                $driver = new \Be\F\Db\Driver\Oracle($name);
                break;
        }

        self::$cache[$cid][$name] = $driver;
        Gc::register($cid, self::class);
        return $driver;
    }

    /**
     * 新创建一个数据库对象
     *
     * @param string $name 数据库名
     * @return \Be\F\Db\Driver
     * @throws DbException
     */
    public static function newInstance($name = 'master')
    {
        $config = ConfigFactory::getInstance('System.Db');
        if (!isset($config->$name)) {
            throw new DbException('数据库配置项（' . $name . '）不存在！');
        }
        $configData = $config->$name;

        $driver = null;
        switch ($configData['driver']) {
            case 'mysql':
                $driver = new \Be\F\Db\Driver\Mysql($name);
                break;
            case 'Mssql':
                $driver = new \Be\F\Db\Driver\Mssql($name);
                break;
            case 'Oracle':
                $driver = new \Be\F\Db\Driver\Oracle($name);
                break;
        }
        return $driver;
    }

    /**
     * 回收资源
     */
    public static function release()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid])) {
            foreach (self::$cache[$cid] as $name => $driver) {
                if (isset(self::$pools[$name])) {
                    $pool = self::$pools[$name];

                    /**
                     * @var Driver $driver
                     */
                    $pdo = $driver->getConnection()->getPdo();
                    $driver->release();

                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $pool->put($pdo);
                }
            }
        }
    }

}
