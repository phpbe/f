<?php

namespace Be\Framework\Util;

class Str
{

    /**
     * 下划线转驼峰，不特殊处理首字母
     *
     * @param string $str
     * @param mixed $ucFirst 是否转换首字母大小写， null：不转换、true：转为大写、false：转为小写
     * @return string
     */
    public static function underline2Camel($str, $ucFirst = null)
    {
        $str = str_replace([
            '_a', '_b', '_c', '_d', '_e', '_f', '_g', '_h', '_i', '_j', '_k', '_l', '_m', '_n', '_o', '_p', '_q', '_r', '_s', '_t', '_u', '_v', '_w', '_x', '_y', '_z'
        ], [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ], $str);

        if ($ucFirst === true) {
            return ucfirst($str);
        } elseif ($ucFirst === false) {
            return lcfirst($str);
        }

        return $str;
    }


    /**
     * 下划线转首字母大写驼峰
     *
     * @param string $str
     * @return string
     */
    public static function underline2CamelUcFirst($str)
    {
        return self::underline2Camel($str, true);
    }


    /**
     * 下划线转首字母小写驼峰
     *
     * @param string $str
     * @return string
     */
    public static function underline2CamelLcFirst($str)
    {
        return self::underline2Camel($str, false);
    }


    /**
     * 驼峰转下划线
     *
     * @param string $str
     * @param bool $trimFirst 是否修前第一个字符
     * @return string
     */
    public static function camel2Underline($str, $trimFirst = true)
    {
        $str = str_replace([
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ], [
            '_a', '_b', '_c', '_d', '_e', '_f', '_g', '_h', '_i', '_j', '_k', '_l', '_m', '_n', '_o', '_p', '_q', '_r', '_s', '_t', '_u', '_v', '_w', '_x', '_y', '_z'
        ], $str);

        if ($trimFirst && substr($str, 0, 1) == '_') $str = substr($str, 1);

        return $str;
    }


    /**
     * 驼峰转连字号（中划线）
     *
     * @param string $str
     * @param bool $trimFirst 是否修前第一个字符
     * @return string
     */
    public static function camel2Hyphen($str, $trimFirst = true)
    {
        $str = str_replace([
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ], [
            '-a', '-b', '-c', '-d', '-e', '-f', '-g', '-h', '-i', '-j', '-k', '-l', '-m', '-n', '-o', '-p', '-q', '-r', '-s', '-t', '-u', '-v', '-w', '-x', '-y', '-z'
        ], $str);

        if ($trimFirst && substr($str, 0, 1) == '-') $str = substr($str, 1);

        return $str;
    }


    /**
     *
     * 限制字符串宽度
     * 名词说明
     * 字符: 一个字符占用一个字节， strlen 长度为 1
     * 文字：(可以看成由多个字符组成) 占用一个或多个字节  strlen 长度可能为 1,2,3,4,5,6
     *
     * @param string $string 要限制的字符串
     * @param int $length 限制的宽度
     * @param string $etc 结层符号
     * @return string
     */
    public static function limit($string, $length = 50, $etc = '...')
    {
        $string = strip_tags($string);
        $length *= 2; //按中文时宽度应加倍


        if (strlen($string) <= $length) return $string;

        $length -= strlen($etc); // 去除结尾符长度
        if ($length <= 0) return '';

        $strLen = strlen($string);

        $pos = 0; // 当前处理到的字符位置
        $lastLen = 0; // 最后一次处理的字符所代表的文字的宽度
        $len = 0; // 文字宽度累加值


        while ($pos < $strLen) // 系统采用了utf-8编码， 逐字符判断
        {
            $char = ord($string[$pos]);
            if ($char == 9 || $char == 10 || (32 <= $char && $char <= 126)) {
                $lastLen = 1;
                $pos++;
                $len++;
            } elseif (192 <= $char && $char <= 223) {
                $lastLen = 2;
                $pos += 2;
                $len += 2;
            } elseif (224 <= $char && $char <= 239) {
                $lastLen = 3;
                $pos += 3;
                $len += 2;
            } elseif (240 <= $char && $char <= 247) {
                $lastLen = 4;
                $pos += 4;
                $len += 2;
            } elseif (248 <= $char && $char <= 251) {
                $lastLen = 5;
                $pos += 5;
                $len += 2;
            } elseif ($char == 252 || $char == 253) {
                $lastLen = 6;
                $pos += 6;
                $len += 2;
            } else {
                $pos++;
            }

            if ($len >= $length) break;
        }

        // 超过指定宽度， 减去最后一次处理的字符所代表的文字宽度
        if ($len >= $length) {
            $pos -= $lastLen;
            $string = substr($string, 0, $pos);
            $string .= $etc;
        }

        return $string;
    }


}
