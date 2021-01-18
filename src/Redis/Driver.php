<?php

namespace Be\F\Redis;

/**
 * Redis 类
 */
class Driver
{

    private $handler = null; // 数据库连接

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 连接数据库
     *
     * @throws RedisException
     */
    public function connect()
    {
        if ($this->handler === null) {

            if (!extension_loaded('Redis')) throw new RedisException('服务器未安装 Redis 扩展！');

            $config = $this->config;

            $handler = new \Redis();
            if (isset($config['timeout']) && $config['timeout'] > 0) {
                if (!$handler->connect($config['host'], $config['port'], $config['timeout'])) {
                    throw new RedisException('连接Redis（' . $config['host'] . ':' . $config['port'] . '）失败！');
                }
            } else {
                if (!$handler->connect($config['host'], $config['port'])){
                    throw new RedisException('连接Redis（' . $config['host'] . ':' . $config['port'] . '）失败！');
                }
            }

            if (isset($config['auth']) && $config['auth'] != '') {
                if (!$handler->auth($config['auth'])) {
                    throw new RedisException('Redis（' . $config['host'] . ':' . $config['port'] . '）验证密码失败！');
                }
            }

            if (isset($config['db']) && $config['db'] != 0) {
                $handler->select($config['db']);
            }

            $this->handler = $handler;
        }
    }



    /**
     * 获取 redis 实例
     *
     * @return \redis
     */
    public function getHandler()
    {
        $this->connect();
        return $this->handler;
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
        $this->connect();
        return call_user_func_array(array($this->handler, $fn), $args);
    }

}
