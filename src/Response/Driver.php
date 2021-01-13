<?php

namespace Be\F\Response;


/**
 * Class Driver
 * @package Be\F\Response
 */
class Driver
{

    protected $data = []; // 暂存数据

    /**
     * @var \Swoole\Http\Response
     */
    protected $response = null;

    /**
     * Response constructor.
     * @param \Swoole\Http\Response $response
     */
    public function __construct(\Swoole\Http\Response $response)
    {
        $this->response = $response;
    }

    /**
     * 请求状态
     *
     * @param int $code 状态码
     * @param string $message 状态信息
     */
    public function status(int $code = 302, string $message = '')
    {
        $this->response->status($code, $message);
    }

    /**
     * 请求重定向
     *
     * @param string $url 跳转网址
     * @param int $code 状态码
     */
    public function redirect(string $url, int $code = 302)
    {
        $this->response->redirect($url, $code);
    }

    /**
     * 设置暂存数据
     * @param string $name 名称
     * @param mixed $value 值 (可以是数组或对象)
     */
    public function set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 获取暂存数据
     *
     * @param string $name 名称
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if (isset($this->data[$name])) return $this->data[$name];
        return $default;
    }

    /**
     * 输出头信息
     *
     * @param string $key
     * @param string $val
     */
    public function header(string $key, string $val)
    {
        $this->response->header($key, $val);
    }

    /**
     * 输出内容
     *
     * @param string $string
     */
    public function write(string $string)
    {
        $this->response->write($string);
    }

    /**
     * 以 JSON 输出暂存数据
     */
    public function json($data = null)
    {
        $this->response->header('Content-type', 'application/json');
        if ($data === null) {
            $this->response->end(json_encode($this->data));
        } else {
            $this->response->end(json_encode(array_merge($this->data, $data)));
        }
    }

    /**
     * 结束输出
     *
     * @param string $string 输出内空
     */
    public function end(string $string = '')
    {
        $this->response->end($string);
    }

    /**
     * 设置 Cookie
     *
     * @param string $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function cookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false)
    {
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

}
