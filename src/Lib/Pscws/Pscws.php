<?php
namespace Be\Lib\Pscws;

/**
 *  PSCWS 中文分词库
 *
 * @package Be\Lib\Pscws
 * @author liu12 <i@liu12.com>
 */
class Pscws
{

    private $handler = null;

    // 构造函数
    public function __construct()
    {
        include_once __DIR__ . '/pscws4/pscws4.class.php';
        $this->handler = new \PSCWS4();

        $this->handler->set_charset('utf8');
        $this->handler->set_dict(__DIR__ . '/pscws4/etc/dict.utf8.xdb');
        $this->handler->set_rule(__DIR__ . '/pscws4/etc/rules.utf8.ini');
    }

    // 析构函数
    public function __destruct()
    {
        $this->handler->close();
    }

    public function setCharset($charset = 'utf8')
    {
        $this->handler->set_charset($charset);
    }

    // 设置词典
    public function setDict($path)
    {
        $this->handler->set_dict($path);
    }

    // 设置规则集
    public function set_rule($path)
    {
        $this->handler->set_rule($path);
    }


    // 设置忽略符号与无用字符
    public function setIgnore($ignore)
    {
        $this->handler->set_ignore($ignore);
    }

    // 设置复合分词等级 ($level = 0,15)
    public function setMulti($level)
    {
        $this->handler->set_multi($level);
    }

    // 设置是否显示分词调试信息
    public function setDebug($bool)
    {
        $this->handler->set_debug($bool);
    }

    // 设置是否自动将散字二元化
    public function setDuality($bool)
    {
        $this->handler->set_duality($bool);
    }

    // 设置要分词的文本字符串
    public function sendText($text)
    {
        $this->handler->send_text($text);
    }

    // 取回一批分词结果(需要多次调用, 直到返回 false)
    public function getValue()
    {
        return $this->handler->get_value();
    }

    // 取回频率和权重综合最大的前 N 个词
    public function getTops($limit = 10, $xattr = '')
    {
        return $this->handler->get_tops($limit, $xattr);
    }


    public function close()
    {
        $this->handler->close();
    }
}
