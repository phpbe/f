<?php

namespace Be\F;

use Be\F\Config\ConfigFactory;
use Be\F\Logger\LoggerFactory;
use Be\F\Property\PropertyFactory;
use Be\F\Request\RequestFactory;
use Be\F\Response\ResponseFactory;
use Be\F\Runtime\RuntimeException;
use Be\F\Runtime\RuntimeFactory;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    /**
     * 获取运行时对象
     *
     * @return Runtime\Driver
     */
    public static function getRuntime()
    {
        return RuntimeFactory::getInstance();
    }

    /**
     * 获取请求对象
     *
     * @return \Be\F\Request\Driver
     */
    public static function getRequest()
    {
        return RequestFactory::getInstance();
    }

    /**
     * 获取输出对象
     *
     * @return \Be\F\Response\Driver
     */
    public static function getResponse()
    {
        return ResponseFactory::getInstance();
    }

    /**
     * 获取指定的配置文件（单例）
     *
     * @param string $name 配置文件名
     * @return mixed
     */
    public static function getConfig($name)
    {
        return ConfigFactory::getInstance($name);
    }

    /**
     * 新创建一个指定的配置文件
     *
     * @param string $name 配置文件名
     * @return mixed
     */
    public static function newConfig($name)
    {
        return ConfigFactory::newInstance($name);
    }

    /**
     * 获取日志记录器
     *
     * @return \Be\F\Logger\Driver
     */
    public static function getLogger()
    {
        return LoggerFactory::getInstance();
    }

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return \Be\F\Property\Driver
     * @throws RuntimeException
     */
    public static function getProperty($name)
    {
        return PropertyFactory::getInstance($name);
    }

    /**
     * 调用未声明的方法
     *
     * @param $name
     * @param $arguments
     * @return null
     */
    public static function __callStatic($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        $module = substr($name, 3);
        $factory = '\\Be\\Framework\\' . $module . '\\' . $module . 'Factory';
        if ($prefix == 'get') {
            if (is_callable([$factory, 'getInstance'])) {
                return $factory::getInstance(...$arguments);
            }
        } elseif ($prefix == 'new') {
            if (is_callable([$factory, 'newInstance'])) {
                return $factory::newInstance(...$arguments);
            }
        }

        return null;
    }

}
