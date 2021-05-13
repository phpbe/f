<?php

namespace Be\F\Cache\Driver;

use Be\F\Cache\Config;
use Be\F\Cache\Driver;
use Be\F\Redis\RedisFactory;

/**
 * 缓存驱动
 */
class Redis implements Driver
{

    /**
     * @var \Redis
     */
    private $redis = null;

    /**
     * 构造函数
     *
     * @param Config $config 配置参数
     */
    public function __construct($config)
    {
        $this->redis = RedisFactory::getInstance($config->redis);
    }

    /**
     * 关闭
     *
     * @return bool
     */
    public function close()
    {
        $this->redis = null;
        return true;
    }

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key 键名
     * @return mixed|false
     */
    public function get($key)
    {
        $value = $this->redis->get('cache:' . $key);
        if (is_bool($value) || is_numeric($value)) return $value;
        return unserialize($value);
    }

    /**
     * 获取 多个指定的缓存 值
     *
     * @param array $keys 键名 数组
     * @return array()
     */
    public function getMany($keys)
    {
        $return = array();

        $prefixedKeys = array();
        foreach ($keys as $key) {
            $prefixedKeys[] = 'cache:' . $key;
        }

        $values = $this->redis->mget($prefixedKeys);
        foreach ($values as $index => $value) {
            if (!is_bool($value) && !is_numeric($value)) {
                $value = unserialize($value);
            }
            $return[$keys[$index]] = $value;
        }

        return $return;
    }

    /**
     * 设置缓存
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int $expire 有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        if (!is_bool($value) && !is_numeric($value)) {
            $value = serialize($value);
        }

        if ($expire > 0) {
            return $this->redis->setex('cache:' . $key, $expire, $value);
        } else {
            return $this->redis->set('cache:' . $key, $value);
        }
    }

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int $expire 有效时间（秒）
     * @return bool
     */
    public function setMany($values, $expire = 0)
    {
        $formattedValues = array();
        foreach ($values as $key => $value) {
            if (!is_bool($value) && !is_numeric($value)) {
                $formattedValues['cache:' . $key] = $value;
            } else {
                $formattedValues['cache:' . $key] = serialize($value);
            }
        }

        if ($expire > 0) {
            $this->redis->multi(); // 开启事务
            $this->redis->mset($formattedValues);
            foreach ($formattedValues as $key => $val) {
                $this->redis->expire($key, $expire);
            }
            return $this->redis->exec();
        } else {
            return $this->redis->mset($formattedValues);
        }
    }

    /**
     * 指定键名的缓存是否存在
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function has($key)
    {
        return $this->redis->exists('cache:' . $key) ? true : false;
    }

    /**
     * 删除指定键名的缓存
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function delete($key)
    {
        return $this->redis->del('cache:' . $key);
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function increment($key, $step = 1)
    {
        return $this->redis->incrby('cache:' . $key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     *
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function decrement($key, $step = 1)
    {
        return $this->redis->decrby('cache:' . $key, $step);
    }

    /**
     * 清除缓存
     *
     * @return bool
     */
    public function flush()
    {
        return true;
    }

    /**
     * 缓存代理
     *
     * @param string $name 键名
     * @param callable $callable 匿名函数，无参数
     * @param int $expire 超时时间
     * @return mixed
     */
    public function proxy($name, $callable, $expire = 0)
    {
        $key = 'cache:proxy:' . $name;
        if ($this->redis->exists($key)) {
            $value = $this->redis->get($key);
            if (is_bool($value) || is_numeric($value)) return $value;
            return unserialize($value);
        }

        $value = $callable();
        if (!is_bool($value) && !is_numeric($value)) $value = serialize($value);

        if ($expire > 0) {
            $this->redis->setex('cache:proxy:' . $key, $expire, $value);
        } else {
            $this->redis->set('cache:proxy:' . $key, $value);
        }

        return $value;
    }

}
