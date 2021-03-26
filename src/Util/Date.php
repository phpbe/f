<?php
namespace Be\F\Util;

class Date
{

    /**
     * 获取后一个月的日期
     *
     * @param string $date 日期 例：2000-01-31
     * @return string 日期 例：2000-02-29
     */
    public static function getNextMonth($date)
    {
        return self::getNextNMonth($date, 1);
    }

    /**
     * 获取后N个月的日期
     *
     * @param string $date 日期 例：2000-01-31
     * @param int $n 月数
     * @return string 日期 例：2000-03-31
     */
    public static function getNextNMonth($date, $n = 1)
    {
        $t = strtotime($date);
        $year = date('Y', $t);
        $month = date('n', $t);
        $day = date('j', $t);

        $month += $n;
        if ($month > 12) {
            while ($month > 12) {
                $year++;
                $month -= 12;
            }
        } elseif ($month <= 0) {
            while ($month < 0) {
                $year--;
                $month += 12;
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

        $t2 = mktime(0, 0, 0, $month, $day, $year);
        return date('Y-m-d', $t2);
    }

    /**
     * 获取前个月
     *
     * @param string $date 日期 例：2000-03-31
     * @return string 日期 例：2000-02-29
     */
    public static function getLastMonth($date)
    {
        return self::getNextNMonth($date, -1);
    }

    /**
     * 获取前N个月
     *
     * @param string $date 日期 例：2000-03-31
     * @param int $n 月数
     * @return string 日期 例：2000-01-31
     */
    public static function getLastNMonth($date, $n = 1)
    {
        return self::getNextNMonth($date, -$n);
    }

    /**
     * 当前时间
     *
     * @param string $format
     * @return false|string
     */
    public static function now($format = 'Y-m-d')
    {
        return date($format, time());
    }

}
