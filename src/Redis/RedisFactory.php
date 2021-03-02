<?php

namespace Be\F\Redis;

use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeException;


/**
 * Redis 工厂
 */
abstract class RedisFactory
{

    private static $cache = [];

    private static $pools = null;

    public static function init()
    {
        if (self::$pools === null) {
            self::$pools = [];
            $config = ConfigFactory::getInstance('System.Redis');
            foreach ($config as $k => $v) {
                $size = isset($v['pool']) ? intval($v['pool']) : 0;
                if ($size <= 0) {
                    continue;
                }

                $redisConfig = new \Swoole\Database\RedisConfig();

                $redisConfig->withHost($v['host']);

                if (isset($v['port'])) {
                    $redisConfig->withPort($v['port']);
                }

                if (isset($v['auth']) && $v['auth']) {
                    $redisConfig->withAuth($v['auth']);
                }

                if (isset($v['db']) && $v['db']) {
                    $redisConfig->withDbIndex($v['db']);
                }

                if (isset($v['timeout']) && $v['timeout']) {
                    $redisConfig->withTimeout($v['timeout']);
                }

                self::$pools[$k] = new \Swoole\Database\RedisPool($redisConfig, $size);
            }
        }
    }

    /**
     * 获取Redis对象
     *
     * @param string $name Redis名
     * @return \Be\F\Redis\Driver
     * @throws RuntimeException
     */
    public static function getInstance($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid][$name])) return self::$cache[$cid][$name];

        $config = ConfigFactory::getInstance('System.Redis');
        if (!isset($config->$name)) {
            throw new RuntimeException('Redis配置项（' . $name . '）不存在！');
        }

        $driver = null;
        if (isset(self::$pools[$name])) {
            $pool = self::$pools[$name];
            $redis = $pool->get();
            $driver = new \Be\F\Redis\Driver($name, $redis);
        } else {
            $driver = new \Be\F\Redis\Driver($name);
        }

        self::$cache[$cid][$name] = $driver;
        return self::$cache[$cid][$name];
    }

    /**
     * 新创建一个Redis对象
     *
     * @param string $name Redis名
     * @return \Be\F\Redis\Driver
     * @throws RuntimeException
     */
    public static function newInstance($name = 'master')
    {
        $config = ConfigFactory::getInstance('System.Redis');
        if (!isset($config->$name)) {
            throw new RuntimeException('Redis配置项（' . $name . '）不存在！');
        }

        return new \Be\F\Redis\Driver($name);
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
                    $redis = $driver->getRedis();
                    $driver->release();
                    $pool->put($redis);
                }
            }
        }
    }

}
