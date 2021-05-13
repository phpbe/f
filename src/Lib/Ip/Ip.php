<?php

namespace Be\F\Lib\Ip;

use Be\F\Lib\LibException;
use Be\F\Request\RequestFactory;

/**
 * 转换到地理位置（QQwry纯真地址库）
 *
 * @package Be\F\Lib\Ip
 * @author liu12 <i@liu12.com>
 */
class Ip
{

    private $db = null;

    private $startPos = 0;
    private $endPos = 0;

    /**
     * 构造函数
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->db = @fopen(__DIR__ . '/ip.dat', 'rb');
        if (!$this->db) {
            throw new LibException('无法读取IP地址库');
        }

        $buffer = fread($this->db, 4);
        $this->startPos = implode('', unpack('L', $buffer));
        if ($this->startPos < 0) $this->startPos += pow(2, 32);

        $buffer = fread($this->db, 4);
        $this->end_pos = implode('', unpack('L', $buffer));
        if ($this->endPos < 0) $this->endPos += pow(2, 32);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->db !== null && is_resource($this->db)) fclose($this->db);
    }

    /**
     * 转换IP 到地理位置
     *
     * @param $ip
     * @return string
     */
    public function convert($ip)
    {
        if (!preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", $ip)) return 'IP 地址错误！';

        $ip = ip2long($ip);
        if ($ip < 0) $ip += 4294967296;

        $start_pos = 0;
        $end_pos = ($this->endPos - $this->startPos) / 7 + 1;

        $ip1 = $ip2 = 0;
        while ($ip1 > $ip || $ip2 < $ip) {
            $middle_pos = intval(($end_pos + $start_pos) / 2);

            fseek($this->db, $this->startPos + 7 * $middle_pos);

            $buffer = fread($this->db, 4);
            if (strlen($buffer) < 4) return '系统错误';
            $ip1 = implode('', unpack('L', $buffer));
            if ($ip1 < 0) $ip1 += 4294967296;
            if ($ip1 > $ip) {
                $end_pos = $middle_pos;
                continue;
            }

            $buffer = fread($this->db, 3);
            if (strlen($buffer) < 3) return '系统错误';
            $seek = implode('', unpack('L', $buffer . chr(0)));
            fseek($this->db, $seek);

            $buffer = fread($this->db, 4);
            if (strlen($buffer) < 4) return '系统错误';
            $ip2 = implode('', unpack('L', $buffer));
            if ($ip2 < 0) $ip2 += 4294967296;
            if ($ip2 < $ip) {
                if ($middle_pos == $start_pos) return '未知位置';
                $start_pos = $middle_pos;
            }
        }

        $addr1 = $addr2 = '';

        $flag = fread($this->db, 1);
        if ($flag == chr(1)) {
            $buffer = fread($this->db, 3);
            if (strlen($buffer) < 3) return '系统错误';
            $ipSeek = implode('', unpack('L', $buffer . chr(0)));
            fseek($this->db, $ipSeek);
            $flag = fread($this->db, 1);
        }

        if ($flag == chr(2)) {
            $buffer = fread($this->db, 3);
            if (strlen($buffer) < 3) return '系统错误';
            $flag = fread($this->db, 1);
            if ($flag == chr(2)) {
                $buffer2 = fread($this->db, 3);
                if (strlen($buffer2) < 3) return '系统错误';
                $seek = implode('', unpack('L', $buffer2 . chr(0)));
                fseek($this->db, $seek);
            } else {
                fseek($this->db, -1, SEEK_CUR);
            }

            while (($char = fread($this->db, 1)) != chr(0))
                $addr2 .= $char;

            $seek = implode('', unpack('L', $buffer . chr(0)));
            fseek($this->db, $seek);

            while (($char = fread($this->db, 1)) != chr(0))
                $addr1 .= $char;
        } else {
            fseek($this->db, -1, SEEK_CUR);
            while (($char = fread($this->db, 1)) != chr(0))
                $addr1 .= $char;

            $flag = fread($this->db, 1);
            if ($flag == chr(2)) {
                $buffer = fread($this->db, 3);
                if (strlen($buffer) < 3) return '系统错误';
                $seek = implode('', unpack('L', $buffer . chr(0)));
                fseek($this->db, $seek);
            } else {
                fseek($this->db, -1, SEEK_CUR);
            }
            while (($char = fread($this->db, 1)) != chr(0)) {
                $addr2 .= $char;
            }
        }

        if (preg_match('/http/i', $addr2)) $addr2 = '';
        $addr = "$addr1 $addr2";
        $addr = iconv('GB2312', 'UTF-8', $addr);
        $addr = preg_replace('/CZ88.Net/is', '', $addr);
        $addr = preg_replace('/^s*/is', '', $addr);
        $addr = preg_replace('/s*$/is', '', $addr);
        if (preg_match('/http/i', $addr) || $addr == '') $addr = '未知位置';

        return $addr;
    }

    public function toString()
    {
        $ip = RequestFactory::getInstance()->getIp();
        return $this->convert($ip);
    }

}

