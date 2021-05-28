<?php

namespace Be\F\Template;

use Be\F\Config\ConfigFactory;
use Be\F\Gc;
use Be\F\Property\PropertyFactory;
use Be\F\Runtime\RuntimeFactory;

/**
 * Template 工厂
 */
abstract class TemplateFactory
{

    private static $cache = [];

    /**
     * 获取指定的一个模板（单例）
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return Driver
     */
    public static function getInstance($template, $theme = null)
    {
        $cid = \Swoole\Coroutine::getuid();
        $parts = explode('.', $template);
        $type = array_shift($parts);
        $name = array_shift($parts);

        $configSystem = ConfigFactory::getInstance('System.System');

        if ($theme === null) {
            $property = PropertyFactory::getInstance($type . '.' . $name);
            if (isset($property->theme)) {
                $theme = $property->theme;
            } else {
                $theme = $configSystem->theme;
            }
        }

        if (isset(self::$cache[$cid][$theme][$template])) return self::$cache[$cid][$theme][$template];

        $runtime = RuntimeFactory::getInstance();
        $frameworkName = $runtime->getFrameworkName();
        $path = $runtime->getCachePath() . '/Template/' . $theme . '/' . $type . '/' . $name . '/' . implode('/', $parts) . '.php';
        if (!file_exists($path)) {
            TemplateHelper::update($template, $theme);
        }

        $class = 'Be\\' . $frameworkName . '\\Cache\\Template\\' . $theme . '\\' . $type . '\\' . $name . '\\' . implode('\\', $parts);
        self::$cache[$cid][$theme][$template] = new $class();
        Gc::register($cid, self::class);
        return self::$cache[$cid][$theme][$template];
    }

    /**
     * 回收资源
     */
    public static function release()
    {
        $cid = \Swoole\Coroutine::getuid();
        unset(self::$cache[$cid]);
    }

}
