<?php

namespace Be\Framework\Response;

use Be\Framework\Be;


/**
 * Class Driver
 * @package Be\Framework\Response
 */
class Driver
{

    protected $data = []; // 暂存数据

    /**
     * @var \Swoole\Http\Response
     */
    private $response = null;

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
     * 成功
     *
     * @param string $message 消息
     * @param string $redirectUrl 跳转网址
     * @param int $redirectTimeout 跳转超时时长
     */
    public function success(string $message, string $redirectUrl = null, int $redirectTimeout = 3)
    {
        $this->set('success', true);
        $this->set('message', $message);

        if ($redirectUrl !== null) {
            $this->set('redirectUrl', $redirectUrl);
            if ($redirectTimeout > 0) $this->set('redirectTimeout', $redirectTimeout);
        }

        $request = Be::getRequest();
        if ($request->isAjax()) {
            $this->json();
        } else {
            $this->display('App.System.System.success');
        }
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param string $redirectUrl 跳转网址
     * @param int $redirectTimeout 跳转超时时长
     */
    public function error(string $message, string $redirectUrl = null, int $redirectTimeout = 3)
    {
        $this->set('success', false);
        $this->set('message', $message);

        if ($redirectUrl !== null) {
            $this->set('redirectUrl', $redirectUrl);
            if ($redirectTimeout > 0) $this->set('redirectTimeout', $redirectTimeout);
        }

        $request = Be::getRequest();
        if ($request->isAjax()) {
            $this->json();
        } else {
            $this->display('App.System.System.error');
        }
    }

    /**
     * 系统异常
     *
     * @param \Throwable $e 错误码
     */
    public function exception(\Throwable $e)
    {
        $request = Be::getRequest();
        if ($request->isAjax()) {
            $this->set('success', false);
            $this->set('message', $e->getMessage());
            $this->set('trace', $e->getTrace());
            $this->set('code', $e->getCode());
            $this->json();
        } else {
            $this->set('e', $e);
            $this->display('App.System.System.exception');
        }
    }

    /**
     * 记录历史节点
     *
     * @param string $historyKey 历史节点键名
     */
    public function createHistory(string $historyKey = null)
    {
        $request = Be::getRequest();
        if ($historyKey === null) {
            $historyKey = $request->app() . '.' . $request->controller();
        }

        $session = Be::getSession();
        $session->set('_history_url_'.$historyKey, $request->server('REQUEST_URI'));
        $session->set('_history_post_'.$historyKey, serialize($request->post()));
    }

    /**
     * 成功
     *
     * @param string $message 消息
     * @param string $historyKey 历史节点键名
     * @param int $redirectTimeout 跳转超时时长
     */
    public function successAndBack(string $message, string $historyKey = null, int $redirectTimeout = 3)
    {
        $request = Be::getRequest();
        if ($historyKey === null) {
            $historyKey = $request->app() . '.' . $request->controller();
        }

        $this->set('success', true);
        $this->set('message', $message);
        $this->set('historyKey', $historyKey);

        $session = Be::getSession();
        $historyUrl = null;
        if ($session->has('_history_url_'.$historyKey)) {
            $historyUrl = $session->get('_history_url_'.$historyKey);
        }
        if (!$historyUrl) $historyUrl = $request->server('HTTP_REFERER');
        if (!$historyUrl) $historyUrl = './';

        $historyPost = null;
        if ($session->has('_history_post_'.$historyKey)) {
            $historyPost = $session->get('_history_post_'.$historyKey);
            if ($historyPost) $historyPost = unserialize($historyPost);
        }

        $this->set('historyUrl', $historyUrl);
        $this->set('historyPost', $historyPost);
        $this->set('redirectTimeout', $redirectTimeout);
        $this->display('App.System.System.successAndBack');
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param string $historyKey 历史节点键名
     * @param int $redirectTimeout 跳转超时时长
     */
    public function errorAndBack(string $message, string $historyKey = null, int $redirectTimeout = 3)
    {
        $request = Be::getRequest();
        if ($historyKey === null) {
            $historyKey = $request->app() . '.' . $request->controller();
        }

        $this->set('success', false);
        $this->set('message', $message);
        $this->set('historyKey', $historyKey);

        $session = Be::getSession();
        $historyUrl = null;
        if ($session->has('_history_url_'.$historyKey)) {
            $historyUrl = $session->get('_history_url_'.$historyKey);
        }
        if (!$historyUrl) $historyUrl = $request->server('HTTP_REFERER');
        if (!$historyUrl) $historyUrl = './';

        $historyPost = null;
        if ($session->has('_history_post_'.$historyKey)) {
            $historyPost = $session->get('_history_post_'.$historyKey);
            if ($historyPost) $historyPost = unserialize($historyPost);
        }

        $this->set('historyUrl', $historyUrl);
        $this->set('historyPost', $historyPost);
        $this->set('redirectTimeout', $redirectTimeout);
        $this->display('App.System.System.errorAndBack');
    }

    /**
     * 显示模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     */
    public function display(string $template = null, string $theme = null)
    {
        if ($template === null) {
            $request = Be::getRequest();
            $app = $request->app();
            $controller = $request->controller();
            $action = $request->action();
            $template = 'App.' . $app . '.' . $controller . '.' . $action;
        }

        $this->response->end($this->fetch($template, $theme));
    }

    /**
     * 获取模板内容
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return  string
     */
    public function fetch(string $template, string $theme = null)
    {
        ob_start();
        ob_clean();
        $templateInstance = Be::getTemplate($template, $theme);
        foreach ($this->data as $key => $val) {
            $templateInstance->$key = $val;
        }
        $templateInstance->display();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
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
