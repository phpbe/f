<?php
namespace Be\Lib\Image\Driver;

use Be\Lib\Image\Driver;

/**
 * 图像处理库 GD 引擎
 *
 * @package Be\Lib\Image\Driver
 * @author liu12 <i@liu12.com>
 */
class GdImpl implements Driver
{
    private $image = null;
    private $type = null;
    private $width = 0;
    private $height = 0;

    // 构造函数
    public function __construct()
    {
    }


    // 析构函数
    public function __destruct()
    {
        if ($this->image) imagedestroy($this->image);
    }

    /**
     * 打开图像
     *
     * @param string $path 图像路径
     * @return bool
     */
    public function open($path)
    {
        $imageInfo = getimagesize($path);

        if ($imageInfo && is_array($imageInfo)) {
            $this->type = $imageInfo['mime'];
            $this->width = $imageInfo[0];
            $this->height = $imageInfo[1];
            $this->image = $this->load($path, $this->type);

            return false;
        }
        return false;
    }

    /**
     * 裁切图像
     *
     * @param int $x 裁切范围左上角 X 坐标
     * @param int $y 裁切范围左上角 Y 坐标
     * @param int | null $width 裁切范围宽度，为 null 取始点至图像右边缘宽度
     * @param int | null $height 裁切范围高度，为 null 取始点至图像上边缘高度
     * @return bool
     */
    public function crop($x = 0, $y = 0, $width = null, $height = null)
    {
        if (!$this->image) return false;
        if ($x > $this->width || $y > $this->height) return false;

        if (!$width) $width = $this->width;
        if (!$height) $height = $this->height;

        if (($x + $width) > $this->width) {
            $width = $this->width - $x;
        }

        if (($y + $height) > $this->height) {
            $height = $this->height - $y;
        }

        $newImage = imagecreatetruecolor($width, $height);
        if ($this->type == 'image/gif') {
            $color = imagecolortransparent($this->image);
            imagepalettecopy($this->image, $newImage);
            imagefill($newImage, 0, 0, $color);
            imagecolortransparent($newImage, $color);
            imagetruecolortopalette($newImage, true, 255);
            imagecopyresized($newImage, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        } elseif ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $color = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $color);
            imagecopyresampled($newImage, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        } else {
            imagealphablending($newImage, false);
            imagecopyresampled($newImage, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        }

        imagedestroy($this->image);

        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;

        return true;
    }

    /**
     * 更改图像大小
     *
     * @param int $width 宽度
     * @param int $height 高度
     * @param string $fit 适应大小方式
     *
     * 	'force': 把图片强制变形成 $width X $height 大小
     * 	'scale': 按比例在安全框 $width X $height 内缩放图片, 输出缩放后图像大小 不完全等于 $width X $height
     * 	'scaleFill': 按比例在安全框 $width X $height 内缩放图片，安全框内没有像素的地方填充色, 使用此参数时可设置背景填充色 $fillColor = array(255,255,255)(红,绿,蓝, 透明度(0透明))
     * 	'图像方位值'：输出指定位置部分图像字母与图像的对应关系如下:
     * 	    northWest    north    northEast
     * 	    west         center        east
     * 	    southWest    south    southEast
     *
     *   当 $fit 值为 'force','scale','scaleFill' 时, 输出图像是完整的，其它值时图像会被截取
     *   当 $fit 值不在取值范围内时，按方位 "center" 处理
     *
     * @param array $fillColor 填充色
     * @return bool
     */
    public function resize($width = 100, $height = 100, $fit = 'center', $fillColor = array(255, 255, 255, 127))
    {
        if (!$this->image) return false;

        $srcX = 0;
        $srcY = 0;
        $destX = 0;
        $destY = 0;
        $srcW = $this->width;
        $srcH = $this->height;
        $destW = $newWidth = $width;
        $destH = $newHeight = $height;

        switch ($fit) {
            case 'force':
                break;
            case 'scale':
                if ($this->width * $height > $this->height * $width) {
                    $destH = $newHeight = intval($this->height * $width / $this->width);
                } else {
                    $destW = $newWidth = intval($this->width * $height / $this->height);
                }
                break;
            case 'scaleFill':
                if ($this->width * $height > $this->height * $width) {
                    $destH = intval($this->height * $width / $this->width);
                    $destY = intval(($height - $destH) / 2);
                } else {
                    $destW = intval($this->width * $height / $this->height);
                    $destX = intval(($width - $destW) / 2);
                }
                break;
            default:

                if ($this->width * $height > $this->height * $width) {
                    $srcW = $width * $this->height / $height;
                } else {
                    $srcH = $height * $this->width / $width;
                }

                switch ($fit) {
                    case 'northWest':
                        $srcX = 0;
                        $srcY = 0;
                        break;
                    case 'north':
                        $srcX = intval(($this->width - $srcW) / 2);
                        $srcY = 0;
                        break;
                    case 'northEast':
                        $srcX = $this->width - $srcW;
                        $srcY = 0;
                        break;
                    case 'west':
                        $srcX = 0;
                        $srcY = intval(($this->height - $srcH) / 2);
                        break;
                    case 'center':
                        $srcX = intval(($this->width - $srcW) / 2);
                        $srcY = intval(($this->height - $srcH) / 2);
                        break;
                    case 'east':
                        $srcX = $this->width - $srcW;
                        $srcY = intval(($this->height - $srcH) / 2);
                        break;
                    case 'southWest':
                        $srcX = 0;
                        $srcY = $this->height - $srcH;
                        break;
                    case 'south':
                        $srcX = intval(($this->width - $srcW) / 2);
                        $srcY = $this->height - $srcH;
                        break;
                    case 'southEast':
                        $srcX = $this->width - $srcW;
                        $srcY = $this->height - $srcH;
                        break;
                    default:
                        $srcX = intval(($this->width - $srcW) / 2);
                        $srcY = intval(($this->height - $srcH) / 2);
                }

                break;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->type == 'image/gif') {
            imagepalettecopy($this->image, $newImage);
            if ($fillColor[3]) {
                $color = imagecolorallocatealpha($newImage, $fillColor[0], $fillColor[1], $fillColor[2], $fillColor[3]);
                imagefill($newImage, 0, 0, $color);
                imagecolortransparent($newImage, $color);
            } else {
                $color = imagecolorallocate($newImage, $fillColor[0], $fillColor[1], $fillColor[2]);
                imagefill($newImage, 0, 0, $color);
            }
            imagetruecolortopalette($newImage, true, 255);
            imagecopyresized($newImage, $this->image, $destX, $destY, $srcX, $srcY, $destW, $destH, $srcW, $srcH);
        } elseif ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $color = imagecolorallocatealpha($newImage, $fillColor[0], $fillColor[1], $fillColor[2], $fillColor[3]);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $color);
            imagecopyresampled($newImage, $this->image, $destX, $destY, $srcX, $srcY, $destW, $destH, $srcW, $srcH);
        } else {
            imagealphablending($newImage, false);
            if ($fit == 'scaleFill') {
                $color = imagecolorallocate($newImage, $fillColor[0], $fillColor[1], $fillColor[2]);
                imagefill($newImage, 0, 0, $color);
            }
            imagecopyresampled($newImage, $this->image, $destX, $destY, $srcX, $srcY, $destW, $destH, $srcW, $srcH);
        }

        imagedestroy($this->image);

        $this->image = $newImage;
        $this->width = $newWidth;
        $this->height = $newHeight;

        return true;
    }

    /**
     * 添加水印图片
     *
     * @param string $path 水印图像绝对路径
     * @param int $x 添加水印点X坐标
     * @param int $y 添加水印点Y坐标
     * @return bool
     */
    public function watermark($path, $x = 0, $y = 0)
    {
        if (!$this->image) return false;

        $imageInfo = getimagesize($path);

        if ($imageInfo && is_array($imageInfo)) {
            $type = $imageInfo['mime'];
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $image = $this->load($path, $type);

            if (!$image) return false;
            imagecopymerge($this->image, $image, $x, $y, 0, 0, $width, $height, 100);
            return true;
        }
        return false;
    }


    /**
     * 添加水印文字
     *
     * @param string $text 文字内容
     * @param int $x 添加水印点X坐标
     * @param int $y 添加水印点Y坐标
     * @param int $angle 旋转角度
     * @param array $style 样式
     * @return bool
     */
    public function text($text, $x = 0, $y = 0, $angle = 0, $style = array())
    {
        $font = isset($style['font']) ? $style['font'] : (__DIR__ . '/fzxbsjw.ttf');
        $font_size = isset($style['fontSize']) ? $style['fontSize'] : 20;
        $color = isset($style['color']) ? imagecolorallocate($this->image, $style['color'][0], $style['color'][1], $style['color'][2]) : imagecolorallocate($this->image, 64, 64, 64);

        imagettftext($this->image, $font_size, $angle, $x, $y, $color, $font, $text);

        return true;
    }

    /**
     * 保存到指定路径
     *
     * @param string $path 要存放的位置的绝对咱径
     * @return bool
     */
    public function save($path)
    {
        if (!$this->image) return false;

        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagepng($this->image, $path);
        } elseif ($this->type == 'image/gif') {
            imagegif($this->image, $path);
        } else {
            imagejpeg($this->image, $path, 80);
        }
        return true;
    }


    /**
     * 直接输出图像二进制内容
     *
     * @param bool $header 是否输出图像HTTP头信息
     * @return bool
     */
    public function output($header = true)
    {
        if (!$this->image) return false;
        if ($header) header('Content-type: ' . $this->type);
        if ($this->type == 'image/png' || $this->type == 'image/x-png') {
            imagepng($this->image);
        } elseif ($this->type == 'image/gif') {
            imagegif($this->image);
        } else {
            imagejpeg($this->image, null, 80);
        }
        return true;
    }

    /**
     * 获取图像宽度像素
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * 获取图像高度像素
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * 设置图像类型， 默认与源类型一致
     *
     * @param $type
     */
    public function setType($type)
    {
        switch ($type) {
            case 'png':
                $this->type = 'image/png';
                break;
            case 'gif':
                $this->type = 'image/gif';
                break;
            default:
                $this->type = 'image/jpg';
                break;
        }
    }

    /**
     * 获取源图像类型
     *
     * @return string
     */
    public function getType()
    {
        if (!$this->type) return 'unknown';

        if ($this->type == 'image/png' || $this->type == 'image/x-png') {
            return 'png';
        } elseif ($this->type == 'image/gif') {
            return 'gif';
        }
        return 'jpg';
    }

    /**
     * 当前处理的对象是否为合法的图片
     *
     * @return bool
     */
    public function isImage()
    {
        if ($this->image)
            return true;
        else
            return false;
    }

    // 加载图像
    private function load($path, $type)
    {
        $image = null;
        // jpeg
        if (function_exists('imagecreatefromjpeg') && (($type == 'image/jpg') || ($type == 'image/jpeg') || ($type == 'image/pjpeg'))) {
            $image = @imagecreatefromjpeg($path);
               if ($image !== false) {
                return $image;
            }
        }

        // png
        if (function_exists('imagecreatefrompng') && (($type == 'image/png') || ($type == 'image/x-png'))) {
            $image = @imagecreatefrompng($path);
            if ($image !== false) {
                return $image;
            }
        }

        // gif
        if (function_exists('imagecreatefromgif') && (($type == 'image/gif'))) {
            $image = @imagecreatefromgif($path);
            if ($image !== false) {
                return $image;
            }
        }

        // gd
        if (function_exists('imagecreatefromgd')) {
            $image = imagecreatefromgd($path);
            if ($image !== false) {
                return $image;
            }
        }

        // gd2
        if (function_exists('imagecreatefromgd2')) {
            $image = @imagecreatefromgd2($path);
            if ($image !== false) {
                return $image;
            }
        }

        // bmp
        if (function_exists('imagecreatefromwbmp')) {
            $image = @imagecreatefromwbmp($path);
            if ($image !== false) {
                return $image;
            }
        }

        return $image;
    }


}
