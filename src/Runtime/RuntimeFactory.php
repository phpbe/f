<?php

namespace Be\Framework\Runtime;

/**
 * Runtime工厂
 */
abstract class RuntimeFactory
{

    /**
     * @var Driver
     */
    private static $instance = null;

    /**
     * 获取Runtime实例
     *
     * @return Driver
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * 设置Runtime实例
     *
     * @param $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

}
