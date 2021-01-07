<?php
namespace Be\Framework\Cache;

use Be\Framework\Be;
use Be\Framework\Exception\CacheException;

/**
 * 缓存驱动
 */
class Driver
{

    /**
     * @var object
     */
    protected $handler = null;

    /**
     * 构造函数
     * @throws CacheException
     */
    public function __construct()
    {
        if (!extension_loaded('Redis')) throw new CacheException('服务器未安装 redis 扩展！');

        $this->handler = new \Redis();

        $config = Be::getConfig('System.Cache');
        if (!$this->handler->connect($config->host, $config->port, $config->timeout)) {
            throw new \RuntimeException('Cache连接Redis（' . $config->host . ':' . $config->port . '）失败！');
        }

        if ('' != $config->auth) {
            if (!$this->handler->auth($config->auth)) {
                throw new \RuntimeException('Cache验证Redis（' . $config->host . ':' . $config->port . '）密码失败！');
            }
        }

        if (0 != $config->db) $this->handler->select($config->db);
    }

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key 键名
     * @return mixed|false
     */
    public function get($key)
    {
        $value = $this->handler->get('cache:'.$key);
        if ($value ===false) return false;
        if (is_numeric($value)) return $value;
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
            $prefixedKeys[] = 'cache:'.$key;
        }

        $values = $this->handler->mget($prefixedKeys);

        foreach ($values as $index => $value) {
            if (!is_numeric($value) && $value !== false)
                $value = unserialize($value);

            $return[$keys[$index]] = $value;
        }

        return $return;
    }

    /**
     * 设置缓存
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        if (!is_numeric($value)) $value = serialize($value);
        if ($expire>0) {
            return $this->handler->setex('cache:'.$key, $expire, $value);
        } else {
            return $this->handler->set('cache:'.$key, $value);
        }
    }

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function setMany($values, $expire = 0)
    {
        $formattedValues = array();
        foreach ($values as $key=>$value) {

            if (!is_numeric($value)) {
                $formattedValues['cache:'.$key] = $value;
            } else {
                $formattedValues['cache:'.$key] = serialize($value);
            }
        }

        if ($expire>0) {
            $this->handler->multi(); // 开启事务
            $this->handler->mset($formattedValues);
            foreach ($formattedValues as $key=>$val) {
                $this->handler->expire($key, $expire);
            }
            return $this->handler->exec();
        } else {
            return $this->handler->mset($formattedValues);
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
        return $this->handler->exists('cache:'.$key) ? true : false;
    }

    /**
     * 删除指定键名的缓存
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function delete($key)
    {
        return $this->handler->del('cache:'.$key);
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @param string    $key 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function increment($key, $step = 1)
    {
        return $this->handler->incrby('cache:'.$key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     *
     * @param string    $key 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function decrement($key, $step = 1)
    {
        return $this->handler->decrby('cache:'.$key, $step);
    }

    /**
     * 清除缓存
     *
     * @return bool
     */
    public function flush()
    {
        return $this->handler->flushDB();
    }

}
