<?php

namespace Be\F\Session\Driver;

use Be\F\Config\ConfigFactory;
use Be\F\Redis\RedisFactory;
use Be\F\Session\Driver;

/**
 * Session
 */
class Redis extends Driver
{

    /**
     * @var \redis
     */
    private $redis = null;

    public function read()
    {
        if ($this->data === null) {
            $this->redis = RedisFactory::getInstance(ConfigFactory::getInstance('System.Session')->redis);
            $data = $this->redis->get('session:' . $this->id);
            if ($data) {
                $data = unserialize($data);
            } else {
                $data = [];
            }
            $this->data = $data;
        }
    }

    public function write()
    {
        if ($this->data !== null) {
            $this->redis->setex('session:' . $this->id, $this->expire, serialize($this->data));
        }
    }

    public function close()
    {
        $this->write();
        $this->redis = null;
        $this->data = null;
    }

    /**
     * 销毁 session
     *
     * @return bool
     */
    public function destroy()
    {
        $this->data = null;
        return $this->redis->del('session:' . $this->id);
    }

}
