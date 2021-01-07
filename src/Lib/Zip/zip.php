<?php
namespace Be\Lib\Zip;

use Be\Framework\Lib\LibException;

/**
 *  zip 压缩文件处理
 *
 * @package Be\Lib\Zip
 * @author liu12 <i@liu12.com>
 */
class Zip
{
    private $path = null; // 压缩包路径
    private $data = null; // 二进制数据
    private $info = null; // 压缩包信息

    private $file_header = "\x50\x4b\x03\x04";
    private $dir_header = "\x50\x4b\x01\x02";
    private $dir_end = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    private $methods = array(0x0 => 'None', 0x1 => 'Shrunk', 0x2 => 'Super Fast', 0x3 => 'Fast', 0x4 => 'Normal', 0x5 => 'Maximum', 0x6 => 'Imploded', 0x8 => 'Deflated');

    /**
     * 构造函数
     *
     * Zip constructor.
     */
    public function __construct()
    {
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 载入压缩包文件
     *
     * @param string $path 压缩包文件路径
     */
    public function open($path)
    {
        $this->path = $path;
    }

    /**
     * 加载压缩包数据
     */
    public function loadData()
    {
        $this->data = file_get_contents($this->path);
    }

    /**
     * 设置压缩包数据
     * @param string $data 数据
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * 解压缩到
     *
     * @param string $dir 解压到的目录
     * @return bool
     * @throws \Exception
     */
    public function extractTo($dir)
    {
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        if (!$this->data) $this->loadData();
        if (!$this->info) $this->loadInfo();

        if (!extension_loaded('zlib')) {
            throw new LibException('你的服务器不支持 zlib');
        }

        for ($i = 0, $n = count($this->info); $i < $n; $i++) {
            if (substr($this->info[$i]['name'], -1, 1) != '/' && substr($this->info[$i]['name'], -1, 1) != '\\') {
                $buffer = $this->getFileData($i);
                $extractToPath = $dir . '/' . str_replace('\\', '/', $this->info[$i]['name']);
                $extractToDir = dirname($extractToPath);

                if (!file_exists($extractToDir)) mkdir($extractToDir, 0777, true);
                file_put_contents($extractToPath, $buffer);
            }
        }
        return true;
    }

    /**
     * 获取数据
     *
     * @param $key
     * @return bool|mixed|string
     */
    public function getFileData($key)
    {
        if ($this->info[$key]['_method'] == 0x8) {
            if (extension_loaded('zlib')) {
                return @ gzinflate(substr($this->data, $this->info[$key]['_dataStart'], $this->info[$key]['csize']));
            }
        } elseif ($this->info[$key]['_method'] == 0x0) {
            return substr($this->data, $this->info[$key]['_dataStart'], $this->info[$key]['csize']);
        } elseif ($this->info[$key]['_method'] == 0x12) {
            if (extension_loaded('bz2')) {
                return bzdecompress(substr($this->data, $this->info[$key]['_dataStart'], $this->info[$key]['csize']));
            }
        }
        return '';
    }

    /**
     * 载入压缩包文件信息
     *
     * @return bool
     * @throws \Exception
     */
    public function loadInfo()
    {
        if (!$this->data) $this->loadData();

        $entries = array();

        $last = strpos($this->data, $this->dir_end);
        do {
            $l = $last;
        } while (($last = strpos($this->data, $this->dir_end, $last + 1)) !== false);

        $offset = 0;
        if ($l) {
            $end_of_central_directory = unpack('vNumberOfDisk/vNoOfDiskWithStartOfCentralDirectory/vNoOfCentralDirectoryEntriesOnDisk/vTotalCentralDirectoryEntries/VSizeOfCentralDirectory/VCentralDirectoryOffset/vCommentLength', substr($this->data, $l + 4));
            $offset = $end_of_central_directory['CentralDirectoryOffset'];
        }

        $start = strpos($this->data, $this->dir_header, $offset);
        do {
            if (strlen($this->data) < $start + 31) {
                throw new LibException('zip文件数据错误');
            }
            $info = unpack('vMethod/VTime/VCRC32/VCompressed/VUncompressed/vLength', substr($this->data, $start + 10, 20));
            $name = substr($this->data, $start + 46, $info['Length']);

            $entries[$name] = array('attr' => null, 'crc' => sprintf("%08s", dechex($info['CRC32'])), 'csize' => $info['Compressed'], 'date' => null, '_dataStart' => null, 'name' => $name, 'method' => $this->methods[$info['Method']], '_method' => $info['Method'], 'size' => $info['Uncompressed'], 'type' => null);
            $entries[$name]['date'] = mktime((($info['Time'] >> 11) & 0x1f), (($info['Time'] >> 5) & 0x3f), (($info['Time'] << 1) & 0x3e), (($info['Time'] >> 21) & 0x07), (($info['Time'] >> 16) & 0x1f), ((($info['Time'] >> 25) & 0x7f) + 1980));

            if (strlen($this->data) < $start + 43) {
                throw new LibException('zip文件数据错误');
            }
            $info = unpack('vInternal/VExternal', substr($this->data, $start + 36, 6));

            $entries[$name]['type'] = ($info['Internal'] & 0x01) ? 'text' : 'binary';
            $entries[$name]['attr'] = (($info['External'] & 0x10) ? 'D' : '-') . (($info['External'] & 0x20) ? 'A' : '-') . (($info['External'] & 0x03) ? 'S' : '-') . (($info['External'] & 0x02) ? 'H' : '-') . (($info['External'] & 0x01) ? 'R' : '-');
        } while (($start = strpos($this->data, $this->dir_header, $start + 46)) !== false);

        $start = strpos($this->data, $this->file_header);
        do {
            if (strlen($this->data) < $start + 34) {
                throw new LibException('zip文件数据错误');
            }
            $info = unpack('vMethod/VTime/VCRC32/VCompressed/VUncompressed/vLength/vExtraLength', substr($this->data, $start + 8, 25));
            $name = substr($this->data, $start + 30, $info['Length']);
            $entries[$name]['_dataStart'] = $start + 30 + $info['Length'] + $info['ExtraLength'];
        } while (strlen($this->data) > $start + 30 + $info['Length'] && ($start = strpos($this->data, $this->file_header, $start + 30 + $info['Length'])) !== false);

        $this->info = array_values($entries);
        return true;
    }

    /**
     * 检测压缩包是否合法
     *
     * @return bool
     */
    public function valid()
    {
        if (!$this->data) $this->loadData();
        if (strpos($this->data, $this->file_header) !== false) return true;
        return false;
    }

}
