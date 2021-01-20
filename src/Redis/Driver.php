<?php

namespace Be\F\Redis;

use Be\F\Config\ConfigFactory;

/**
 * Redis 类
 */
class Driver
{

    /**
     * @var \Redis
     */
    private $redis = null; // 数据库连接

    public function __construct($name, $redis = null)
    {
        $this->name = $name;
        if ($redis === null) {

            $config = ConfigFactory::getInstance('System.Redis');
            if (!isset($config->$name)) {
                throw new RedisException('数据库配置项（' . $name . '）不存在！');
            }
            $config = $config->$name;

            $redis = new \Redis();
            if (isset($config['timeout']) && $config['timeout'] > 0) {
                if (!$redis->connect($config['host'], $config['port'], $config['timeout'])) {
                    throw new RedisException('连接Redis（' . $config['host'] . ':' . $config['port'] . '）失败！');
                }
            } else {
                if (!$redis->connect($config['host'], $config['port'])){
                    throw new RedisException('连接Redis（' . $config['host'] . ':' . $config['port'] . '）失败！');
                }
            }

            if (isset($config['auth']) && $config['auth'] != '') {
                if (!$redis->auth($config['auth'])) {
                    throw new RedisException('Redis（' . $config['host'] . ':' . $config['port'] . '）验证密码失败！');
                }
            }

            if (isset($config['db']) && $config['db'] != 0) {
                $redis->select($config['db']);
            }

            $this->redis = $redis;
        } else {
            $this->redis = $redis;
        }
    }

    /**
     * 获取 redis 实例
     *
     * @return \redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * 封装 redis 方法
     *
     * @param string $fn redis 扩展方法名
     * @param array() $args 传入的参数
     * @return mixed
     */
    public function __call($fn, $args)
    {
        return call_user_func_array(array($this->redis, $fn), $args);
    }

    /**
     * 关闭
     */
    public function close() {
        $this->redis->close();
    }

    /**
     * 释放，释放后可被连接池回收
     */
    public function release() {
        $this->redis = null;
    }

}
