<?php

namespace Be\F\Util\FileSystem;

class FileSize
{

    /**
     * 文件大小转换 整型转字符
     *
     * @param int $fileSizeInt 文件大小整型
     * @return string
     */
    public static function int2String($fileSizeInt)
    {
        $fileSizeString = null;
        if ($fileSizeInt > 1099511627776) {
            if ($fileSizeInt % 1099511627776 == 0) {
                $fileSizeString = ($fileSizeInt >> 40) . ' TB';
            } else {
                $fileSizeString = number_format($fileSizeInt / 1099511627776, 2, '.', '') . ' TB';
            }
        } elseif ($fileSizeInt > 1073741824) {
            if ($fileSizeInt % 1073741824 == 0) {
                $fileSizeString = ($fileSizeInt >> 30) . ' GB';
            } else {
                $fileSizeString = number_format($fileSizeInt / 1073741824, 2, '.', '') . ' GB';
            }
        } elseif ($fileSizeInt > 1048576) {
            if ($fileSizeInt % 1048576 == 0) {
                $fileSizeString = ($fileSizeInt >> 20) . ' MB';
            } else {
                $fileSizeString = number_format($fileSizeInt / 1048576, 2, '.', '') . ' MB';
            }
        } elseif ($fileSizeInt > 1024) {
            $fileSizeString = ($fileSizeInt >> 10) . ' KB';
        } else {
            $fileSizeString = $fileSizeInt . ' B';
        }
        return $fileSizeString;
    }

    /**
     * 文件大小转换 字符转整型
     *
     * @param string $fileSizeString 文件大小字符
     * @return int
     * @throws \Exception
     */
    public static function string2Int($fileSizeString)
    {
        $fileSizeInt = 0;
        $fileSizeString = strtoupper(trim($fileSizeString));

        $unit = substr($fileSizeString, -2, 1);
        $size = null;
        if (is_numeric($unit)) {
            $unit = substr($fileSizeString, -1);
            $size = trim(substr($fileSizeString, 0, -1));
        } else {
            $unit = trim(substr($fileSizeString, -2));
            $size = trim(substr($fileSizeString, 0, -2));
        }

        if (is_numeric($unit)) {
            $fileSizeInt = (int)$fileSizeString;
        } else {

            if (!in_array($unit, ['B', 'K', 'M', 'G', 'T', 'P', 'KB', 'MB', 'GB', 'TB', 'PB'])) {
                // 不支持的文件尺寸单位
                throw new \Exception('Not support file size unit: ' . $unit . '!');
            }

            switch ($unit) {
                case 'B':
                    $fileSizeInt = $size;
                    break;
                case 'K':
                case 'KB':
                    $fileSizeInt = $size << 10;
                    break;
                case 'M':
                case 'MB':
                    $fileSizeInt = $size << 20;
                    break;
                case 'G':
                case 'GB':
                    $fileSizeInt = $size << 30;
                    break;
                case 'T':
                case 'TB':
                    $fileSizeInt = $size << 40;
                    break;
                case 'P':
                case 'PB':
                    $fileSizeInt = $size << 50;
                    break;
            }
        }


        return $fileSizeInt;
    }

}
