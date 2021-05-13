<?php

namespace Be\F\Db\Connection;

use Be\F\Config\ConfigFactory;
use Be\F\Db\Connection;
use Be\F\Db\DbException;

/**
 * 连接器
 */
class Mysql extends Connection
{

    public function __construct($name, $pdo = null)
    {
        $this->name = $name;
        if ($pdo === null) {

            $config = ConfigFactory::getInstance('System.Db');
            if (!isset($config->$name)) {
                throw new DbException('数据库配置项（' . $name . '）不存在！');
            }
            $config = $config->$name;

            $options = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            );

            if (isset($config['options'])) {
                $options = $config['options'] + $options;
            }

            // 设置默认编码为 UTF-8
            if (empty($config['charset'])) {
                $config['charset'] = 'utf8mb4';
            }

            $dsn = null;
            if (isset($config['dsn']) && $config['dsn']) {
                $dsn = $config['dsn'];
            } else {
                $dsn = 'mysql:dbname=' . $config['name'] . ';host=' . $config['host'] . ';port=' . $config['port'] . ';charset=' . $config['charset'];
            }

            $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
            if (!$pdo) throw new DbException('连接MySQL数据库' . $config['name'] . '（' . $config['host'] . '） 失败！');

            $pdo->query('SET NAMES ' . $config['charset']);

            $this->pdo = $pdo;
        } else {
            $this->pdo = $pdo;
        }
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
            $field = str_replace('.', '`.`', $field);
        }

        return '`' . $field . '`';
    }

    /**
     * 处理插入数据库的字符串值，防注入, 仅处理敏感字符，不加外层引号，
     * 与 quote 方法的区别可以理解为 quoteValue 比 escape 多了最外层的引号
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        return addslashes($value);
    }


}
