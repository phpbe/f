<?php
namespace Be\F\Lib\Image;

use Be\F\Lib\LibException;

/**
 * 图像处理库
 *
 * @package Be\F\Lib\Image
 * @author liu12 <i@liu12.com>
 */
class Image
{
    private $handler = null;
    private $imagick = false;
    private $gd = false;

    // 构造函数
    public function __construct()
    {
        if (extension_loaded('Imagick')) {
            $this->handler = new \Be\F\Lib\Image\Driver\ImagickImpl();
            $this->imagick = true;
        } elseif (extension_loaded('gd'))  {
            $this->handler = new \Be\F\Lib\Image\Driver\GdImpl();
            $this->gd = true;
        } else {
            throw new LibException('未安装可用的图像扩展（Imagick或GD）！');
        }
    }

    // 析构函数
    public function __destruct()
    {
        $this->handler = null;
    }

    // 检测当前是否为 imagick 处理器
    public function isImagick()
    {
        return $this->imagick;
    }

    // 检测当前是否为  GD 处理器
    public function isGD()
    {
        return $this->gd;
    }

    // 获取处理器
    public function getHandler()
    {
        return $this->handler;
    }

    public function __call($fn, $args)
    {
        return call_user_func_array(array($this->handler, $fn), $args);
    }

}
