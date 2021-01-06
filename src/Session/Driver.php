<?php

namespace Be\Framework\Session;

use Be\Framework\Be;

/**
 * Session
 */
class Driver
{

    /**
     * @var \redis
     */
    private $handler = null;
    private $id = null; // 当前用户的 session id
    private $name = null; // session name
    private $expire = 1440; // session 超时时间
    private $data = null; // session 数据

    /**
     * 获取 session id
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * 获取 session name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * 获取 session 超时时间
     *
     * @return int
     */
    public function expire()
    {
        return $this->expire;
    }


    public function read()
    {
        if ($this->data === null) {
            if (!extension_loaded('Redis')) {
                throw new \RuntimeException('SESSION初始化失败：服务器未安装 Redis 扩展！');
            }

            $this->handler = new \Redis();

            $config = Be::getConfig('System.Session');
            if (!$this->handler->connect($config->host, $config->port, $config->timeout)) {
                throw new \RuntimeException('SESSION连接Redis（' . $config->host . ':' . $config->port . '）失败！');
            }

            if ('' != $config->auth) {
                if (!$this->handler->auth($config->auth)) {
                    throw new \RuntimeException('SESSION验证Redis（' . $config->host . ':' . $config->port . '）密码失败！');
                }
            }

            if (0 != $config->db) $this->handler->select($config->db);

            $data = $this->handler->get('session:' . $this->id);
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
            $this->handler->setex('session:' . $this->id, $this->expire, serialize($this->data));
        }
    }

    // 获取数据库实例
    public function start()
    {
        $config = Be::getConfig('System.Session');
        $this->name = $config->name;
        $this->expire = $config->expire;

        $request = Be::getRequest();
        $sessionId = $request->cookie($this->name, false);
        if (!$sessionId) {
            $sessionId = session_create_id();
            $response = Be::getResponse();
            $response->cookie($this->name, $sessionId, 0, '/', '', false, true);
        }
        $this->id = $sessionId;
    }

    public function close()
    {
        $this->write();
    }

    /**
     * 获取session 值
     *
     * @param string $name 名称
     * @param string $default 默认值
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($this->data === null) {
            $this->read();
        }

        if (isset($this->data[$name])) return $this->data[$name];
        return $default;
    }

    /**
     * 向session中赋值
     *
     * @param string $name 名称
     * @param string $value 值
     */
    public function set($name, $value)
    {
        if ($this->data === null) {
            $this->read();
        }

        $this->data[$name] = $value;
    }

    /**
     * 是否已设置指定名称的 session
     *
     * @param string $name 名称
     * @return bool
     */
    public function has($name)
    {
        if ($this->data === null) {
            $this->read();
        }

        return isset($this->data[$name]);
    }

    /**
     *
     * 删除除指定名称的 session
     * @param string $name 名称
     *
     * @return mixed
     */
    public function delete($name)
    {
        if ($this->data === null) {
            $this->read();
        }

        $value = null;
        if (isset($this->data[$name])) {
            $value = $this->data[$name];
            unset($this->data[$name]);
        }
        return $value;
    }

    /**
     * 销毁 session
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->data === null) {
            $this->read();
            $this->data = null;
        }

        return $this->handler->del('session:' . $this->id);
    }

}
