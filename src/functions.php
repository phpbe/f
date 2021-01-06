<?php
use Be\Framework\Be;


/**
 * 处理网址
 * 启用 SEF 时生成伪静态页， 为空时返回网站网址
 *
 * @param null | string $route 路径（应用名.控制器名.动作名）
 * @param null | array $params
 * @return string 生成的网址
 * @throws \Be\Framework\Exception\RuntimeException
 */
function beUrl($route = null, $params = [])
{
    $request = Be::getRequest();
    if ($route === null) {
        if (count($params) > 0) {
            $route = $request->route();
        } else {
            return $request->rootUrl();
        }
    }

    $configSystem = Be::getConfig('System.System');
    if ($configSystem->urlRewrite) {
        $urlParams = '';
        if (count($params)) {
            foreach ($params as $key => $val) {
                $urlParams .= '/' . $key . '-' . $val;
            }
        }
        return $request->rootUrl() . '/' . str_replace('.', '/', $route) . $urlParams . $configSystem->urlSuffix;
    } else {
        return $request->rootUrl() . '/?route=' . $route . (count($params) > 0 ? '&' . http_build_query($params) : '');
    }
}
