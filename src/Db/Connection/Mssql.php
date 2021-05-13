<?php

namespace Be\F\Db\Connection;

use Be\F\Config\ConfigFactory;
use Be\F\Db\Connection;
use Be\F\Db\DbException;


/**
 * 连接器
 */
class Mssql extends Connection
{

    public function __construct($name, $pdo = null)
    {
        $this->name = $name;

        $config = ConfigFactory::getInstance('System.Db');
        if (!isset($config->$name)) {
            throw new DbException('数据库配置项（' . $name . '）不存在！');
        }
        $config = $config->$name;

        $options = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        );

        if (isset($config['options'])) {
            $options = $config['options'] + $options;
        }

        $dsn = null;
        if (isset($config['dsn']) && $config['dsn']) {
            $dsn = $config['dsn'];
        } else {
            $dsn = 'sqlsrv:Database=' . $config['name'] . ';Server=' . $config['host'];
            if (isset($config['port'])) {
                $dsn .= ',' . $config['port'];
            }
        }

        $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
        if (!$pdo) throw new DbException('连接MSSQL数据库' . $config['name'] . '（' . $config['host'] . '） 失败！');
        $this->pdo = $pdo;
    }

    /**
     * 处理插入数据库的字段名或表名
     *
     * @param string $field
     * @return string
     */
    public function quoteKey($field)
    {
        if (strpos($field, '.')) {
            $field = str_replace('.', '].[', $field);
        }

        return '[' . $field . ']';
    }

    /**
     * 处理插入数据库的字符串值，防注入, 仅处理敏感字符，不加外层引号，
     * 与 quote 方法的区别可以理解为 quote 比 escape 多了最外层的引号
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        $value = str_replace('\'', '\'\'', $value);

        return $value;
    }

}
