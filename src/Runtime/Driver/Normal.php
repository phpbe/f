<?php

namespace Be\Framework\Runtime\Driver;


use Be\Framework\Be;
use Be\Framework\Log;
use Be\Framework\Runtime\Driver;

/**
 *  运行时
 * @package System
 *
 */

/**
 * 标准PHP模式运行时
 *
 * Class Normal
 * @package Be\System\Runtime\Driver
 */
class Normal extends Driver
{

    public function execute()
    {
        $request = new \Be\Framework\Request\Driver\Normal();
        $response = new \Be\Framework\Response\Driver\Normal();
        Be::$cache[0]['Request'] = $request;
        Be::$cache[0]['Response'] = $response;

        // 启动 session
        $session = Be::getSession();
        $session->start();
        register_shutdown_function([$session, 'close']);

        try {
            // 检查网站配置， 是否暂停服务
            $configSystem = Be::getConfig('System.System');

            // 默认时区
            date_default_timezone_set($configSystem->timezone);

            // 启动 session
            Be::getSession()->start();

            $app = null;
            $controller = null;
            $action = null;

            // 从网址中提取出 路径
            if ($configSystem->urlRewrite) {

                //print_r($_SERVER);

                /*
                 * REQUEST_URI 可能值为：[/path]/{action}[/{k-v}].html?[k=v]
                 * 需要解析的有效部分为： {action}[/{k-v}]
                 */
                $uri = $_SERVER['REQUEST_URI'];    // 返回值为:

                // 移除 [/path]
                $scriptName = $_SERVER['SCRIPT_NAME'];
                $indexName = '/index.php';
                $pos = strrpos($scriptName, $indexName);
                if ($pos !== false) {
                    $path = substr($scriptName, 0, $pos);
                    if ($path) {
                        if (strpos($uri, $path) === 0) {
                            $uri = substr($uri, strlen($path));
                        }
                    }
                }

                // 移除 ?[k=v]
                if ($_SERVER['QUERY_STRING'] != ''){
                    $uri = substr($uri, 0, strrpos($uri, '?'));
                }

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

                            $_GET[$key] = $_REQUEST[$key] = $val;
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
                } else {
                    $response->set('code', 404);
                    $response->error('路由参数（' . $route . '）无法识别！');
                }
            }

            $request->setRoute($app, $controller, $action);

            $class = 'Be\\App\\' . $app . '\\Controller\\' . $controller;
            if (!class_exists($class)) {
                $response->set('code', 404);
                $response->error('控制器 ' . $app . '/' . $controller . ' 不存在！');
            }

            $instance = new $class();
            if (method_exists($instance, $action)) {
                $instance->$action();
            } else {
                $response->set('code', 404);
                $response->error('未定义的任务：' . $action);
            }

        } catch (\Throwable $t) {
            $response->exception($t);
            Log::emergency($t);
        }

        $session->close();
    }

}
