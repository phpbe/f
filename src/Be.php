<?php

namespace Be\Framework;

use Be\Framework\App\ServiceFactory;
use Be\Framework\Cache\CacheFactory;
use Be\Framework\Config\ConfigFactory;
use Be\Framework\Logger\LoggerFactory;
use Be\Framework\Property\PropertyFactory;
use Be\Framework\Request\RequestFactory;
use Be\Framework\Response\ResponseFactory;
use Be\Framework\Runtime\RuntimeException;
use Be\Framework\Runtime\RuntimeFactory;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    public static $cache = []; // 缓存资源实例

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
     * @return \Be\Framework\Request\Driver
     */
    public static function getRequest()
    {
        return RequestFactory::getInstance();
    }

    /**
     * 获取输出对象
     *
     * @return \Be\Framework\Response\Driver
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
     * 获取指定的一个服务（单例）
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function getService($name)
    {
        return ServiceFactory::getInstance($name);
    }

    /**
     * 新创建一个服务
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function newService($name)
    {
        return ServiceFactory::newInstance($name);
    }

    /**
     * 获取日志记录器
     *
     * @return \Be\Framework\Logger\Driver
     */
    public static function getLogger()
    {
        return LoggerFactory::getInstance();
    }

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return \Be\Framework\Property\Driver
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
