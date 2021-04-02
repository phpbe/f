<?php

namespace Be\F\Session;

use Be\F\Config\Annotation\BeConfigItem;
use Be\F\Config\ConfigFactory;
use Be\F\Runtime\RuntimeFactory;
use Be\F\Util\Annotation;


/**
 * Session 帮助类
 */
abstract class SessionHelper
{

    public static function getConfigRedisKeyValues()
    {
        $keyValues = [];
        $className = '\\Be\\'.RuntimeFactory::getInstance()->getFrameworkName().'\\App\\System\\Config\\Redis';
        if (class_exists($className)) {
            $reflection = new \ReflectionClass($className);
            $properties = $reflection->getProperties(\ReflectionMethod::IS_PUBLIC);
            foreach ($properties as $property) {
                $itemName = $property->getName();
                $itemComment = $property->getDocComment();
                $parseItemComments = Annotation::parse($itemComment);
                if (isset($parseItemComments['BeConfigItem'][0])) {
                    $annotation = new BeConfigItem($parseItemComments['BeConfigItem'][0]);
                    $configItem = $annotation->toArray();
                    if (isset($configItem['value'])) {
                        $keyValues[$itemName] = $configItem['value'];
                    }
                }
            }
        }

        $config = ConfigFactory::getInstance('System.redis');
        $arrConfig = get_object_vars($config);
        foreach ($arrConfig as $k => $v) {
            if (!isset($keyValues[$k])) {
                $keyValues[$k] = $k;
            }
        }

        return $keyValues;
    }


}
