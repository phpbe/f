<?php

namespace Be\Framework\Request;

/**
 * Request
 */
class Driver
{
    protected $app = null;
    protected $controller = null;
    protected $action = null;
    protected $route = null;

    protected $json = null;

    /**
     * @var \Swoole\Http\Request
     */
    private $request = null;

    public function __construct(\Swoole\Http\Request $request)
    {
        $this->request = $request;
    }

    public function method()
    {
        return $this->request->server['request_method'];
    }

    public function isGet()
    {
        return 'GET' == $this->request->server['request_method'] ? true : false;
    }

    public function isPost()
    {
        return 'POST' == $this->request->server['request_method'] ? true : false;
    }

    public function isAjax()
    {
        return (
        (isset($this->request->header['accept']) && strpos(strtolower($this->request->header['accept']), 'application/json') !== false)
        ) ? true : false;
    }

    /**
     * 获取当前请求的完整网址
     */
    public function url()
    {
        $url = 'http://';
        $url .= $this->request->header['host'];
        $url .= $_SERVER['REQUEST_URI'];
        return $url;
    }

    /**
     * 获取当前请求的完整网址
     */
    public function rootUrl() {
        $url = 'http://';
        $url .= $this->request->header['host'];
        return $url;
    }

    /**
     * 获取data数据目录的网址
     */
    public function dataUrl() {
        return $this->rootUrl() . '/' . Be::getRuntime()->dataDir();
    }

    /**
     * 获取请求者的 IP 地址
     *
     * @return string
     */
    public function ip(bool $detectProxy = true)
    {
        return  $this->request->server['remote_addr'];
    }

    /**
     * 获取 $_GET 数据
     * @param string $name 参数量
     * @param mixed $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function get(string $name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->get, $name, $default, $format);
    }

    /**
     * 获取 $_POST 数据
     * @param string $name 参数量
     * @param mixed $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function post(string $name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->post, $name, $default, $format);
    }

    /**
     * 获取 $_REQUEST 数据
     * @param string $name 参数量
     * @param mixed $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function request(string $name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->request, $name, $default, $format);
    }

    /**
     * 获取 ajax 请求发送的 JSON 数据
     * @param string $name 参数量
     * @param mixed $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function json(string $name = null, $default = null, $format = 'string')
    {
        if ($this->json === null) {
            $json = $this->request->getContent();
            $json = json_decode($json, true);
            if ($json) {
                $this->json = $json;
            } else {
                $this->json = [];
            }
        }

        return $this->_request($this->json, $name, $default, $format);
    }

    /**
     * 获取 $_SERVER 数据
     * @param string $name 参数量
     * @param mixed $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function server(string $name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->server, $name, $default, $format);
    }

    /**
     * 获取 $_COOKIE 数据
     * @param string $name 参数量
     * @param mixed $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function cookie(string $name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->cookie, $name, $default, $format);
    }

    /**
     * 获取上传的文件
     * @param string|null $name 参数量
     * @return array|null
     */
    public function files(string $name = null)
    {
        if ($name === null) {
            return $this->request->files;
        }

        if (!isset($this->request->files[$name])) return null;

        return $this->request->files[$name];
    }

    protected function _request($input, $name, $default, $format)
    {
        if ($name === null) {
            if ($format instanceof \Closure) {
                $input = $this->formatByClosure($input, $format);
            } else {
                if ($format) {
                    $fnFormat = 'format' . ucfirst($format);
                    $input = $this->$fnFormat($input);
                }
            }

            return $input;
        }

        $value = null;
        if (strpos($name, '.') === false) {
            if (!isset($input[$name])) return $default;
            $value = $input[$name];
        } else {
            $tmpValue = $input;
            $names = explode('.', $name);
            foreach ($names as $x) {
                if (!isset($tmpValue[$x])) return $default;
                $tmpValue = $tmpValue[$x];
            }
            $value = $tmpValue;
        }

        if ($format instanceof \Closure) {
            return $this->formatByClosure($value, $format);
        } else {
            if ($format) {
                $fnFormat = 'format' . ucfirst($format);
                return $this->$fnFormat($value);
            } else {
                return $value;
            }
        }
    }

    protected function formatInt($value)
    {
        return is_array($value) ? array_map([$this, 'formatInt'], $value) : intval($value);
    }

    protected function formatFloat($value)
    {
        return is_array($value) ? array_map([$this, 'formatFloat'], $value) : floatval($value);
    }

    protected function formatBool($value)
    {
        return is_array($value) ? array_map([$this, 'formatBool'], $value) : boolval($value);
    }

    protected function formatString($value)
    {
        return is_array($value) ? array_map([$this, 'formatString'], $value) : htmlspecialchars($value);
    }

    // 过滤  脚本,样式，框架
    protected function formatHtml($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'formatHtml'], $value);
        } else {
            $value = preg_replace("@<script(.*?)</script>@is", '', $value);
            $value = preg_replace("@<style(.*?)</style>@is", '', $value);
            $value = preg_replace("@<iframe(.*?)</iframe>@is", '', $value);

            return $value;
        }
    }

    /**
     * 格式化 IP
     * @param $value
     * @return array|string
     */
    protected function formatIp($value)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = $this->formatIp($v);
            }
            return $returns;
        } else {
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            } else {
                return 'invalid';
            }
        }
    }

    protected function formatByClosure($value, \Closure $closure)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = $this->formatByClosure($v, $closure);
            }
            return $returns;
        } else {
            return $closure($value);
        }

    }

    /**
     * 获取当前执行的 APP 名
     *
     * @return null | string
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * 获取当前执行的 控制器 名
     *
     * @return null | string
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * 获取当前执行的 动作 名
     *
     * @return null | string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * 获取当前执行的 路径（应用名.控制器名.动作名）
     *
     * @return null | string
     */
    public function route()
    {
        return $this->route;
    }

    /**
     * 设置当前路径
     *
     * @param string $app 应用名
     * @param string $controller 控制器名
     * @param string $action 动作名
     */
    public function setRoute(string $app, string $controller, string $action)
    {
        $this->app = $app;
        $this->controller = $controller;
        $this->action = $action;
        $this->route = $app . '.' . $controller . '.' . $action;
    }

}

