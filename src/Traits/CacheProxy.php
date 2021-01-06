<?php
namespace Be\Framework\Traits;

/**
 * 缓存代理
 */
abstract class CacheProxy
{
    /**
     * 启动缓存代理
     *
     * @param int $expire 超时时间
     * @return \Be\Framework\Cache\Proxy | Mixed
     */
    public function withCache($expire = 600)
    {
        return new \Be\Framework\Cache\Proxy($this, $expire);
    }

}
