<?php
namespace Be\Framework\Util;

class Date
{



    /**
     * 获取下个月
     *
     * @param string $date 日期 例：2000-01-31
     * @return string 日期 例：2000-02-29
     */
    public static function getNextMonth($date)
    {
        return self::getNextNMonth($date, 1);
    }

    /**
     * 获取下N个月
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


}
