<?php

namespace Be\F\Util;

class Arr
{

    /**
     * 合并数据，支持多维
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function merge($arr1, $arr2)
    {
        $merged = $arr1;

        foreach ($arr2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::merge($merged[$key], $value);
            } elseif (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }


}
