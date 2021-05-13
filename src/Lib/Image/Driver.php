<?php
namespace Be\F\Lib\Image;

/**
 * 图像处理库 接口
 *
 * @package Be\F\Lib\Image
 * @author liu12 <i@liu12.com>
 */
interface Driver
{

    /**
     * 打开图像
     *
     * @param string $path 图像路径
     * @return bool
     */
    public function open($path);

    /**
     * 裁切图像
     *
     * @param int $x 裁切范围左上角 X 坐标
     * @param int $y 裁切范围左上角 Y 坐标
     * @param int | null $width 裁切范围宽度，为 null 取始点至图像右边缘宽度
     * @param int | null $height 裁切范围高度，为 null 取始点至图像上边缘高度
     * @return bool
     */
    public function crop($x = 0, $y = 0, $width = null, $height = null);

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
    public function resize($width = 100, $height = 100, $fit = 'center', $fillColor = array(255, 255, 255, 127));

    /**
     * 添加水印图片
     *
     * @param string $path 水印图像绝对路径
     * @param int $x 添加水印点X坐标
     * @param int $y 添加水印点Y坐标
     * @return bool
     */
    public function watermark($path, $x = 0, $y = 0);


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
    public function text($text, $x = 0, $y = 0, $angle = 0, $style = array());


    /**
     * 保存到指定路径
     *
     * @param string $path 要存放的位置的绝对咱径
     * @return bool
     */
    public function save($path);


    /**
     * 直接输出图像二进制内容
     *
     * @param bool $header 是否输出图像HTTP头信息
     * @return bool
     */
    public function output($header = true);


    /**
     * 获取图像宽度像素
     *
     * @return int
     */
    public function getWidth();


    /**
     * 获取图像高度像素
     *
     * @return int
     */
    public function getHeight();


    /**
     * 设置图像类型， 默认与源类型一致
     *
     * @param $type
     */
    public function setType($type);


    /**
     * 获取源图像类型
     *
     * @return string
     */
    public function getType();

    /**
     * 当前处理的对象是否为合法的图片
     *
     * @return bool
     */
    public function isImage();

}


