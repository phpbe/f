<?php

namespace Be\Lib\Captcha;

/**
 *  验证码库
 *
 * @package Be\Lib\Captcha
 * @author liu12 <i@liu12.com>
 */
class Captcha
{

    private $image = null; // 画布
    private $width = 0; // 宽度
    private $height = 0; // 高度

    private $fontFamily = ''; // 字体
    private $fontSize = 16; // 大小
    private $fontColor = array(0, 0, 0); // 颜色

    private $bgColor = array(255, 255, 255); // 背景颜色

    private $text; // 输出的字符
    private $textLength = 4; // 输出的字符长度

    // 构造函数
    public function __construct()
    {
        $this->fontFamily = __DIR__ . '/verdana.ttf';
    }


    // 析构函数
    public function __destruct()
    {
        if ($this->image) imagedestroy($this->image);
    }

    /**
     * 设置图片大小
     *
     * @param int $width 宽度
     * @param int $height 高度
     */
    public function setSize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * 设置图片宽度
     *
     * @param int $width 宽度
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * 设置图片高度
     *
     * @param int $height 高度
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * 设置字体
     *
     * @param string $fontFamily 字体绝对路径
     */
    public function setFontFamily($fontFamily)
    {
        $this->fontFamily = $fontFamily;
    }

    /**
     * 设置字体大小
     *
     * @param int $fontSize 字体大小
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;
    }

    /**
     * 设置字体颜色
     *
     * @param array $fontColor RGB颜色
     */
    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;
    }

    /**
     * 设置背景色
     *
     * @param array $bgColor RGB颜色
     */
    public function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;
    }

    /**
     * 设置字符长度
     *
     * @param int $textLength 长度
     */
    public function setTextLength($textLength)
    {
        $this->textLength = $textLength;
    }

    /**
     * 初始化
     */
    public function init()
    {
        if ($this->width == 0) {
            $this->width = floor($this->fontSize * 1.3) * $this->textLength + 10;
        }
        if ($this->height == 0) {
            $this->height = $this->fontSize * 2;
        }

        $this->image = imagecreatetruecolor($this->width, $this->height);
        imagefill($this->image, 0, 0, imagecolorallocate($this->image, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]));

        $str = 'abcdefghijkmnpqrstuvwxy3456789';
        $len = strlen($str) - 1;
        for ($i = 0; $i < $this->textLength; $i++) {
            $this->text[] = $str[rand(0, $len)];
        }

        $fontColor = imagecolorallocate($this->image, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);

        for ($i = 0; $i < $this->textLength; $i++) {
            $angle = rand(-1, 1) * rand(1, 30);
            imagettftext($this->image, $this->fontSize, $angle, 5 + $i * floor($this->fontSize * 1.3), floor($this->height * 0.75), $fontColor, $this->fontFamily, $this->text[$i]);
        }
    }

    /**
     * 添加干扰点
     *
     * @param int $n 多少个点
     * @param array | null $color RGB颜色
     */
    public function point($n = 100, $color = null)
    {
        if ($this->image == null) $this->init();

        if (!$color) $color = $this->fontColor;
        $color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        for ($i = 0; $i < $n; $i++) {
            imagesetpixel($this->image, rand(0, $this->width), rand(0, $this->height), $color);
        }
    }


    /**
     * 添加干扰线
     *
     * @param int $n
     * @param array | null $color RGB颜色
     */
    public function line($n = 5, $color = null)
    {
        if ($this->image == null) $this->init();

        if (!$color) $color = $this->fontColor;
        $color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        for ($i = 0; $i < $n; $i++) {
            imageline($this->image, 0, rand(0, $this->width), $this->width, rand(0, $this->height), $color);
        }
    }

    /**
     * 扭曲图像
     */
    public function distortion()
    {
        if ($this->image == null) $this->init();

        $image = imagecreatetruecolor($this->width, $this->height);
        imagefill($image, 0, 0, imagecolorallocate($this->image, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]));
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $color = imagecolorat($this->image, $x, $y);
                imagesetpixel($image, (int)($x + sin($y / $this->height * 2 * M_PI - M_PI * 0.5) * 3), $y, $color);
            }
        }
        imagedestroy($this->image);
        $this->image = $image;
    }

    /**
     * 添加边框
     *
     * @param int $n 宽度
     * @param array | null $color RGB颜色
     */
    public function border($n = 1, $color = null)
    {
        if ($this->image == null) $this->init();

        if (!$color) $color = $this->fontColor;
        $color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        for ($i = 0; $i < $n; $i++) {
            imagerectangle($this->image, $i, $i, $this->width - $i - 1, $this->height - $i - 1, $color);
        }
    }

    /**
     * 输出图像
     *
     * @param string $type
     */
    public function output($type = 'gif')
    {
        if ($this->image == null) $this->init();

        switch ($type) {
            case 'gif':
                header("Content-type: image/gif");
                imagegif($this->image);
                break;
            case 'jpg':
            case 'jpeg':
                header("Content-type: image/jpeg");
                imagejpeg($this->image, '', 0.5);
                break;
            case 'png':
                header("Content-type: image/png");
                imagepng($this->image);
                break;
            case 'bmp':
                header("Content-type: image/vnd.wap.wbmp");
                imagewbmp($this->image);
                break;
            default:
                header("Content-type: image/gif");
                imagegif($this->image);
                break;

        }
    }

    /**
     * 获取输出的字符
     *
     * @return string
     */
    public function toString()
    {
        return is_array($this->text) ? implode('', $this->text) : '';
    }

}
