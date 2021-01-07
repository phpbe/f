<?php
namespace Be\Lib\Http;

use Be\Framework\Lib\LibException;

/**
 * HTTP 封装库
 *
 * @package Be\Lib\Http
 * @author liu12 <i@liu12.com>
 */
class Http
{

    private $options = null;
    private $url = null;
    private $data = null;
    private $header = [];

    /**
     * 构造函数
     *
     * http constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new LibException('您的服务器未安装用于HTTP通信的 CURL 扩展');
        }

        $this->init();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 初始化
     */
    public function init() {
        $this->options = [
            'connectTimeout' => 15,
            'timeout' => 30,
            'redirection' => 5,
            'httpVersion' => 1.0,
            'userAgent' => 'phpbe',
        ];
        $this->url = null;
        $this->data = [];
        $this->header = [];
    }

    /**
     * 设置项
     *
     * @param $name
     * @param $value
     */
    public function option($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * 设置头信息
     *
     * @param $value
     */
    public function header($value)
    {
        $this->header[] = $value;
    }

    /**
     * 验证身份
     *
     * @param $user
     * @param $pass
     */
    public function authorization($user, $pass) {
        $this->header[] = 'Authorization: Basic '.base64_encode($user.':'.$pass);
    }

    /**
     *
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * 设置要传递的一个数据， 键值对形式
     *
     * @param string | array $key 键名
     * @param mixed $val 值
     */
    public function setData($key, $val = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $val;
        }
    }


    /**
     * GET 请求
     *
     * @param $url
     * @return string
     */
    public function get($url)
    {
        $this->options['method'] = 'GET';
        $this->url = $url;
        return $this->request();
    }

    /**
     * POST 请求
     *
     * @param $url
     * @param $data
     * @return string
     */
    public function post($url, $data = [])
    {
        $this->options['method'] = 'POST';
        $this->url = $url;
        $this->data = http_build_query($data);
        return $this->request();
    }

    /**
     * POST 请求，数据为JSON
     *
     * @param $url
     * @param mixed $data
     * @return string
     */
    public function postJson($url, $data)
    {
        $this->options['method'] = 'POST';
        $this->header[] = 'Content-Type: application/json; charset=utf-8';
        $this->url = $url;
        $this->data = json_encode($data);
        return $this->request();
    }

    /**
     * 执行请求
     *
     * @return string
     * @throws \Exception
     */
    private function request()
    {
        $url = parse_url($this->url);
        $ssl = ($url['scheme'] == 'https' || $url['scheme'] == 'ssl');

        $handle = curl_init();

        curl_setopt($handle, CURLOPT_URL, $this->url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $ssl);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($handle, CURLOPT_USERAGENT, $this->options['userAgent']);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $this->options['connectTimeout']);
        curl_setopt($handle, CURLOPT_TIMEOUT, $this->options['timeout']);
        curl_setopt($handle, CURLOPT_MAXREDIRS, $this->options['redirection']);

        if (count($this->header)) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $this->header);
        }

        if (isset($this->options['userpwd'])) {
            // 是否权限认证，用户名：密码
            curl_setopt($handle, CURLOPT_USERPWD, $this->options['userpwd']);
        }

        if ($this->options['method'] == 'POST' && count($this->data)) {
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $this->data);
        }

        if ($this->options['httpVersion'] == '1.0')
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        else
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($handle, CURLOPT_HEADER, false);    //不返回头信息

        $response = curl_exec($handle);

        if (curl_errno($handle)) {
            curl_close($handle);
            throw new LibException('连接主机' . $this->url . '时发生错误: ' . curl_error($handle));
        }

        curl_close($handle);
        return $response;
    }

}

