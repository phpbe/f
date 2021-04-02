<?php

namespace Be\F\Session;

use Be\F\Request\RequestFactory;
use Be\F\Response\ResponseFactory;

/**
 * Session
 */
abstract class Driver
{

    protected $id = null; // 当前用户的 session id
    protected $name = null; // session name
    protected $expire = 1440; // session 超时时间
    protected $data = null; // session 数据

    public function __construct($config)
    {
        $this->name = $config->name;
        $this->expire = $config->expire;
    }

    /**
     * 获取 session id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取 session name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取 session 超时时间
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    abstract function read();

    abstract function write();

    /**
     * 启动 SESSION
     *
     */
    public function start()
    {
        $request = RequestFactory::getInstance();
        $sessionId = $request->cookie($this->name, false);
        if (!$sessionId) {
            $sessionId = session_create_id();
            $response = ResponseFactory::getInstance();
            $response->cookie($this->name, $sessionId, 0, '/', '', false, true);
        }
        $this->id = $sessionId;
    }

    abstract function close();

    /**
     * 获取session 值
     *
     * @param string $name 名称
     * @param string $default 默认值
     * @return mixed
     */
    public function get($name = null, $default = null)
    {
        if ($this->data === null) {
            $this->read();
        }

        if ($name === null) return $this->data;
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
    abstract function destroy();

}
