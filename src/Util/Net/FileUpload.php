<?php

namespace Be\Framework\Util\Net;

class FileUpload
{

    /**
     * 上传错误码描述
     *
     * @param int $errorCode 错误码
     * @return string
     */
    public static function errorDescription($errorCode)
    {
        switch ($errorCode) {
            case 1:
                return '上传的文件过大（超过了 php.ini 中 upload_max_filesize 选项限制的值：' . ini_get('upload_max_filesize') . '）！';
            case 2:
                return '上传的文件过大（超过了 php.ini 中 post_max_size 选项限制的值：' . ini_get('post_max_size') . '）！';
            case 3:
                return '文件只有部分被上传！';
            case 4:
                return '没有文件被上传！';
            case 5:
                return '上传的文件大小为 0！';
            case 6:
                return '找不到临时文件夹！';
            case 7:
                return '文件写入失败！';
            default:
                return '未知错误，代码：' . $errorCode;

        }
    }
}
