<?php

namespace Be\F;

/**
 * 回收
 */
abstract class Gc
{

    private static $classes = [];

    /**
     * 注删新创建的资源的类
     *
     * @param $cid
     * @param $factory
     */
    public static function register($cid, $class)
    {
        self::$classes[$cid][$class] = 1;
    }

    /**
     * 回收资源，调用指定类的 release 释放资源
     */
    public static function release($cid)
    {
        if (isset(self::$classes[$cid])) {
            foreach (self::$classes[$cid] as $class => $val) {
                $class::release();
            }
        }
    }

}
