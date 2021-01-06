<?php
namespace Be\Lib\Css;

/**
 *  CSS处理库
 *
 * @package Be\Lib\Http
 * @author liu12 <i@liu12.com>
 */
class Css
{

    // 构造函数
    public function __construct()
    {
    }

    // 析构函数
    public function __destruct()
    {
    }

    /**
     * 颜色加深
     *
     * @param string $hexColor 16进制颜色（如: FF0000 / #999 / #FFFFFF ）
     * @param int $percent 加深百分比
     * @return string
     */
    public function darken($hexColor, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgbColor = $this->hexToRgb($hexColor);
        $hslColor = $this->rgbToHsl($rgbColor);

        $hslColor[2] = min(100, max(0, $hslColor[2] - $percent));

        return $this->rgbToHex($this->hslToRgb($hslColor));
    }

    /**
     * 颜色减淡
     *
     * @param string $hexColor 16进制颜色（如: FF0000 / #999 / #FFFFFF ）
     * @param int $percent 减淡百分比
     * @return string
     */
    public function lighten($hexColor, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgbColor = $this->hexToRgb($hexColor);
        $hslColor = $this->rgbToHsl($rgbColor);

        $hslColor[2] = min(100, max(0, $hslColor[2] + $percent));

        return $this->rgbToHex($this->hslToRgb($hslColor));
    }

    /**
     * 亮度降低(按剩余亮度的百分比)
     *
     * @param string $hexColor 16进制颜色（如: FF0000 / #999 / #FFFFFF ）
     * @param int $percent 降低百分比
     * @return string
     */
    public function darker($hexColor, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgbColor = $this->hexToRgb($hexColor);
        $hslColor = $this->rgbToHsl($rgbColor);

        $hslColor[2] = $hslColor[2] - $hslColor[2] * $percent / 100;

        return $this->rgbToHex($this->hslToRgb($hslColor));
    }

    /**
     * 亮度升高(按剩余亮度的百分比)
     *
     * @param string $hexColor 16进制颜色（如: FF0000 / #999 / #FFFFFF ）
     * @param int $percent 升高百分比
     * @return string
     */
    public function lighter($hexColor, $percent)
    {
        $percent = floatval($percent);
        if ($percent < 0) $percent = 0;
        if ($percent > 100) $percent = 100;

        $rgbColor = $this->hexToRgb($hexColor);
        $hslColor = $this->rgbToHsl($rgbColor);

        $hslColor[2] = $hslColor[2] + (100 - $hslColor[2]) * $percent / 100;

        return $this->rgbToHex($this->hslToRgb($hslColor));
    }

    /**
     * 16进制字符串颜色转 RGB
     *
     * @param string $hexColor 16进制颜色（如: FF0000 / #999 / #FFFFFF ）
     * @return array
     */
    public function hexToRgb($hexColor)
    {
        $c = array(0, 0, 0);

        if (substr($hexColor, 0, 1) == '#') $hexColor = substr($hexColor, 1);

        $num = hexdec($hexColor);
        $width = strlen($hexColor) == 3 ? 16 : 256;

        for ($i = 2; $i >= 0; $i--) {
            $t = $num % $width;
            $num /= $width;

            $c[$i] = $t * (256 / $width) + $t * floor(16 / $width);
        }

        return $c;
    }

    /**
     * RGB 转 16进制字符串颜色
     *
     * @param array $rgbColor RGB颜色数组
     * @return string
     */
    public function rgbToHex($rgbColor)
    {
        return sprintf("#%02x%02x%02x", $rgbColor[0], $rgbColor[1], $rgbColor[2]);
    }

    /**
     * RGB 颜色转 HSL
     *
     * @param array $rgbColor RGB颜色数组
     * @return array
     */
    public function rgbToHsl($rgbColor)
    {
        $r = $rgbColor[0] / 255;
        $g = $rgbColor[1] / 255;
        $b = $rgbColor[2] / 255;

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        $h = null;

        $l = ($min + $max) / 2;
        if ($min == $max) {
            $s = $h = 0;
        } else {
            if ($l < 0.5)
                $s = ($max - $min) / ($max + $min);
            else
                $s = ($max - $min) / (2.0 - $max - $min);

            if ($r == $max) $h = ($g - $b) / ($max - $min);
            elseif ($g == $max) $h = 2.0 + ($b - $r) / ($max - $min);
            elseif ($b == $max) $h = 4.0 + ($r - $g) / ($max - $min);

        }

        $hslColor = [
            ($h < 0 ? $h + 6 : $h) * 60,
            $s * 100,
            $l * 100,
        ];

        return $hslColor;
    }

    /**
     * HSL 转 RGB
     *
     * @param array $hslColor HSL 颜色数组
     * @return array
     */
    public function hslToRgb($hslColor)
    {
        $H = $hslColor[0] / 360;
        $S = $hslColor[1] / 100;
        $L = $hslColor[2] / 100;

        if ($S == 0) {
            $r = $g = $b = $L;
        } else {
            $temp2 = $L < 0.5 ?
                $L * (1.0 + $S) :
                $L + $S - $L * $S;

            $temp1 = 2.0 * $L - $temp2;

            $r = $this->hslToRgbHelper($H + 1 / 3, $temp1, $temp2);
            $g = $this->hslToRgbHelper($H, $temp1, $temp2);
            $b = $this->hslToRgbHelper($H - 1 / 3, $temp1, $temp2);
        }

        // $rgbColor = [round($r*255), round($g*255), round($b*255)];
        $rgbColor = [$r * 255, $g * 255, $b * 255];
        return $rgbColor;
    }


    private function hslToRgbHelper($comp, $temp1, $temp2)
    {
        if ($comp < 0) $comp += 1.0;
        elseif ($comp > 1) $comp -= 1.0;

        if (6 * $comp < 1) return $temp1 + ($temp2 - $temp1) * 6 * $comp;
        if (2 * $comp < 1) return $temp2;
        if (3 * $comp < 2) return $temp1 + ($temp2 - $temp1) * ((2 / 3) - $comp) * 6;

        return $temp1;
    }

}

