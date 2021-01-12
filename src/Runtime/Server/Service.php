<?php

namespace Be\F\Runtime\Server;

use Be\F\Be;
use Be\F\Exception\RuntimeException;
use Be\F\Log\Driver;

/**
 * 服务
 *
 * @package System
 *
 */
class Service
{
    private $server = null;


    public function __construct()
    {
    }


    public function start()
    {
        if ($this->server !== null) {
            return;
        }

        \Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

        // 检查网站配置， 是否暂停服务
        $configSystem = Be::getConfig('System.System');
        date_default_timezone_set($configSystem->timezone);

        $this->server = new \Swoole\Http\Server("0.0.0.0", 80);
        $this->server->set(['enable_coroutine' => true]);
        $this->server->on('request', function ($swRequest, $swResponse) {
            $swResponse->header('Server', 'BE', false);
            $uri = $swRequest->server['request_uri'];
            $ext = strrchr($uri, '.');
            if ($ext) {
                $ext = strtolower(substr($ext, 1));
                if (isset(self::MIME[$ext])) {
                    $rootPath = Be::getRuntime()->rootPath();
                    if (file_exists($rootPath . $uri)) {
                        $swResponse->header('Content-Type', self::MIME[$ext], false);
                        //缓存
                        $lastModified = gmdate('D, d M Y H:i:s', filemtime($rootPath . $uri)) . ' GMT';
                        if (isset($swRequest->header['if-modified-since']) && $swRequest->header['if-modified-since'] == $lastModified) {
                            $swResponse->status(304);
                            $swResponse->end();
                            return true;
                        }

                        $swResponse->header('Last-Modified', $lastModified, false);

                        //发送Expires头标，设置当前缓存的文档过期时间，GMT格式
                        $swResponse->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT', false);

                        //发送Cache_Control头标，设置xx秒以后文档过时,可以代替Expires，如果同时出现，max-age优先。
                        $swResponse->header('Cache-Control', 'max-age=31536000', false);
                        $swResponse->header('Pragma', 'max-age=31536000', false);

                        $swResponse->sendfile($rootPath . $uri);
                        return true;
                    }

                    if ($uri == '/favicon.ico') {
                        $swResponse->end();
                        return true;
                    }
                }
            }

            $swRequest->request = null;
            if ($swRequest->get !== null) {
                if ($swRequest->post !== null) {
                    $swRequest->request = array_merge($swRequest->get, $swRequest->post);
                } else {
                    $swRequest->request = $swRequest->get;
                }
            } else {
                if ($swRequest->post !== null) {
                    $swRequest->request = $swRequest->post;
                }
            }

            $request = new \Be\F\Request\Driver($swRequest);
            $response = new \Be\F\Response\Driver($swResponse);

            $cid = \Swoole\Coroutine::getuid();
            Be::$cache[$cid]['Request'] = $request;
            Be::$cache[$cid]['Response'] = $response;

            // 启动 session
            $session = Be::getSession();
            $session->start();

            try {

                // 检查网站配置， 是否暂停服务
                $configSystem = Be::getConfig('System.System');

                $app = null;
                $controller = null;
                $action = null;

                // 从网址中提取出 路径
                if ($configSystem->urlRewrite) {

                    // 移除 .html
                    $lenSefSuffix = strlen($configSystem->urlSuffix);
                    if (substr($uri, -$lenSefSuffix, $lenSefSuffix) == $configSystem->urlSuffix) {
                        $uri = substr($uri, 0, strrpos($uri, $configSystem->urlSuffix));
                    }

                    // 移除结尾的 /
                    if (substr($uri, -1, 1) == '/') $uri = substr($uri, 0, -1);

                    // /{action}[/{k-v}]
                    $uris = explode('/', $uri);
                    $len = count($uris);
                    if ($len > 3) {
                        $app = $uris[1];
                        $controller = $uris[2];
                        $action = $uris[3];
                    }

                    if ($len > 4) {
                        /**
                         * 把网址按以下规则匹配
                         * /{action}/{参数名1}-{参数值1}/{参数名2}-{参数值2}/{参数名3}-{参数值3}
                         * 其中{参数名}-{参数值} 值对不限数量
                         */
                        for ($i = 4; $i < $len; $i++) {
                            $pos = strpos($uris[$i], '-');
                            if ($pos !== false) {
                                $key = substr($uris[$i], 0, $pos);
                                $val = substr($uris[$i], $pos + 1);

                                $swRequest->get[$key] = $swRequest->request[$key] = $val;
                            }
                        }
                    }
                }

                // 默认访问控制台页面
                if (!$app) {
                    $route = $request->request('route', $configSystem->home);
                    $routes = explode('.', $route);
                    if (count($routes) == 3) {
                        $app = $routes[0];
                        $controller = $routes[1];
                        $action = $routes[2];
                        $request->setRoute($app, $controller, $action);
                        $class = 'Be\\App\\' . $app . '\\Controller\\' . $controller;
                        if (!class_exists($class)) {
                            $response->set('code', 404);
                            $response->error('控制器 ' . $app . '/' . $controller . ' 不存在！');
                        } else {
                            $instance = new $class();
                            if (method_exists($instance, $action)) {
                                $instance->$action();
                            } else {
                                $response->set('code', 404);
                                $response->error('未定义的任务：' . $action);
                            }
                        }
                    } else {
                        $response->set('code', 404);
                        $response->error('路由参数（' . $route . '）无法识别！');
                     }
                }

            } catch (\Throwable $t) {
                $response->exception($t);
                Log::emergency($t);
            }

            unset(Be::$cache[$cid]);
            $session->close();
        });

        $this->server->start();
    }


    public function stop()
    {
        $this->server->stop();
    }


    public function reload()
    {
        $this->server->reload();
    }

}
