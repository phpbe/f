<?php
namespace Be\Framework\Util;

class Datetime
{
    /**
     * 格式化时间
     *
     * @param string $time 字符型时间， 例如：2000-01-01
     * @param int $maxDays 多少天前或后以默认时间格式输出
     * @param string $defaultFormat 默认时间格式
     * @return string
     */
    public static function formatTime($time, $maxDays = 30, $defaultFormat = 'Y-m-d')
    {
        return self::formatTimestamp(strtotime($time), $maxDays, $defaultFormat);
    }

    /**
     * 格式化时间
     *
     * @param int $timestamp unix 时间戳
     * @param int $maxDays 多少天前或后以默认时间格式输出
     * @param string $defaultFormat 默认时间格式
     * @return string
     */
    public static function formatTimestamp($timestamp, $maxDays = 30, $defaultFormat = 'Y-m-d')
    {
        $t = time();

        $seconds = $t - $timestamp;

        // 如果是{$maxDays}天前，直接输出日期
        $maxSeconds = $maxDays * 86400;
        if ($seconds > $maxSeconds || $seconds < -$maxSeconds) return date($defaultFormat, $timestamp);

        if ($seconds > 86400) {
            $days = intval($seconds / 86400);
            if ($days == 1) {
                if (date('a', $timestamp) == 'am') return '昨天上午';
                else return '昨天下午';
            } elseif ($days == 2) {
                return '前天';
            }
            return $days . '天前';
        } elseif ($seconds > 3600) return intval($seconds / 3600) . '小时前';
        elseif ($seconds > 60) return intval($seconds / 60) . '分钟前';
        elseif ($seconds >= 0) return '刚才';
        elseif ($seconds > -60) return '马上';
        elseif ($seconds > -3600) return intval(-$seconds / 60) . '分钟后';
        elseif ($seconds > -86400) return intval(-$seconds / 3600) . '小时后';
        else {
            $days = intval(-$seconds / 86400);
            if ($days == 1) {
                if (date('a', $timestamp) == 'am') return '明天上午';
                else return '明天下午';
            } elseif ($days == 2) {
                return '后天';
            }
            return $days . '天后';
        }
    }

    /**
     * 获取下个月的时间
     *
     * @param string $datetime 时间 例：2000-01-31 12:00:00
     * @return string 时间 例：2000-02-29 12:00:00
     */
    public static function getNextMonth($datetime)
    {
        return self::getNextNMonth($datetime, 1);
    }

    /**
     * 获取下N个月的时间
     *
     * @param string $datetime 时间 例：2000-01-31 12:00:00
     * @param int $n 月数
     * @return string 时间 例：2000-02-29 12:00:00
     */
    public static function getNextNMonth($datetime, $n = 1)
    {
        $t = strtotime($datetime);
        $year = date('Y', $t);
        $month = date('n', $t);
        $day = date('j', $t);

        $month += $n;
        if ($month > 12) {
            while ($month > 12) {
                $year++;
                $month -= 12;
            }
        }

        switch ($month) {
            case 1:
                if ($day > 31) $day = 31;
                break;
            case 2:
                $maxDay = (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0) ? 29 : 28;
                if ($day > $maxDay) $day = $maxDay;
                break;
            case 3:
                if ($day > 31) $day = 31;
                break;
            case 4:
                if ($day > 30) $day = 30;
                break;
            case 5:
                if ($day > 31) $day = 31;
                break;
            case 6:
                if ($day > 30) $day = 30;
                break;
            case 7:
                if ($day > 31) $day = 31;
                break;
            case 8:
                if ($day > 31) $day = 31;
                break;
            case 9:
                if ($day > 30) $day = 30;
                break;
            case 10:
                if ($day > 31) $day = 31;
                break;
            case 11:
                if ($day > 30) $day = 30;
                break;
            case 12:
                if ($day > 31) $day = 31;
                break;
        }

        $t2 = mktime(date('G', $t), date('i', $t), date('s', $t), $month, $day, $year);
        return date('Y-m-d H:i:s', $t2);
    }

}
