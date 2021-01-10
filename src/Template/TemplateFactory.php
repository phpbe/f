<?php

namespace Be\Framework\Template;

use Be\Framework\Config\ConfigFactory;
use Be\Framework\Property\PropertyFactory;
use Be\Framework\Runtime\RuntimeFactory;


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

        if (isset(self::$cache[$cid]['Template'][$theme][$template])) return self::$cache[$cid]['Template'][$theme][$template];

        $path = RuntimeFactory::getInstance()->getCachePath() . '/Framework/Template/' . $theme . '/' . $type . '/' . $name . '/' . implode('/', $parts) . '.php';
        if ($configSystem->developer || !file_exists($path)) {
            TemplateHelper::update($template, $theme);
        }

        $class = 'Be\\Cache\\Framework\\Template\\' . $theme . '\\' . $type . '\\' . $name . '\\' . implode('\\', $parts);
        self::$cache[$cid]['Template'][$theme][$template] = new $class();
        return self::$cache[$cid]['Template'][$theme][$template];
    }



}
