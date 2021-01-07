<?php

namespace Be\Framework\Cache;


class Proxy
{

    /**
     * 代理的对象
     *
     * @ignore
     * @var mixed
     */
    private $instance = null;

    /**
     * 缓存超时时间
     * @var int
     */
    private $expire = null;

    /**
     * 构造函数
     *
     * @ignore
     * @param mixed $instance
     * @param int $expire
     */
    public function __construct($instance, $expire)
    {
        $this->instance = $instance;
        $this->expire = $expire;
    }

    /**
     * 代理调用具体对象中的方法
     *
     * @ignore
     * @param string $function
     * @param array $arguments
     * @return bool|mixed
     * @throws \Exception
     */
    public function __call($function, $arguments)
    {
        $key = 'CacheProxy:' . get_class($this->instance) . ':' . $function . ':' . md5(serialize($arguments)) . ':' . $this->expire;

        $result = Cache::get($key);
        if ($result) {
            return $result;
        }

        $result = call_user_func_array(array($this->instance, $function), $arguments);

        Cache::set($key, $result, $this->expire);
        return $result;
    }

}