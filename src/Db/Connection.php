<?php

namespace Be\F\Db;

/**
 * 连接器
 */
abstract class Connection
{

    protected $name = null; // 数据库名称

    /**
     * @var \PDO
     */
    protected $pdo = null; // 数据库连接

    /**
     * @var \PDOStatement
     */
    protected $statement = null; // 预编译 sql

    protected $transactions = 0; // 开启的事务数，防止嵌套

    abstract public function __construct($name, $pdo = null);

    /**
     * 获取数据库名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取原生PDO连接
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 关闭，PDO 未提供主动 close 的方法
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * 释放，释放后可被连接池回收
     */
    public function release()
    {
        $this->pdo = null;
    }

    /**
     * 预编译 sql 语句
     *
     * @param string $sql 查询语句
     * @param array $options 参数
     * @return \PDOStatement
     * @throws DbException | \PDOException | \Exception
     */
    public function prepare($sql, array $options = null)
    {
        $statement = null;
        if ($options === null) {
            $statement = $this->pdo->prepare($sql);
        } else {
            $statement = $this->pdo->prepare($sql, $options);
        }
        return $statement;
    }

    /**
     * 执行 sql 语句
     *
     * @param string $sql 查询语句
     * @param array $bind 占位参数
     * @param array $prepareOptions 参数
     * @return \PDOStatement
     * @throws DbException | \PDOException | \Exception
     */
    public function execute($sql, array $bind = null, array $prepareOptions = null)
    {
        if ($bind === null) {
            $statement = $this->pdo->query($sql);
        } else {
            $statement = $this->prepare($sql, $prepareOptions);
            $statement->execute($bind);
        }
        return $statement;
    }

    /**
     * 执行 sql 语句
     *
     * @param string $sql 查询语句
     * @return int 影响的行数
     * @throws DbException | \PDOException | \Exception
     */
    public function query($sql, array $bind = null, array $prepareOptions = null)
    {
        $statement = $this->execute($sql, $bind, $prepareOptions);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();
        return $effectLines;
    }

    /**
     * 获取 insert 插入后产生的 id
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 开启事务处理
     *
     * @throws DbException
     */
    public function startTransaction()
    {
        $this->beginTransaction();
    }

    /**
     * 开启事务处理
     *
     * @throws DbException
     */
    public function beginTransaction()
    {
        $this->transactions++;
        if ($this->transactions == 1) {
            $this->pdo->beginTransaction();
        }
    }

    /**
     * 事务回滚
     *
     * @throws DbException
     */
    public function rollback()
    {
        $this->transactions--;
        if ($this->transactions == 0) {
            $this->pdo->rollBack();
        }
    }

    /**
     * 事务提交
     *
     * @throws DbException
     */
    public function commit()
    {
        $this->transactions--;
        if ($this->transactions == 0) {
            $this->pdo->commit();
        }
    }

    /**
     * 是否在事务中
     *
     * @return bool
     * @throws DbException
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * 获取 版本号
     *
     * @return string
     * @throws DbException
     */
    public function getVersion()
    {
        return $this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * 处理插入数据库的字段名或表名
     *
     * @param string $field
     * @return string
     */
    abstract function quoteKey($field);

    /**
     * 处理多个插入数据库的字段名或表名
     *
     * @param array $fields
     * @return array
     */
    public function quoteKeys($fields)
    {
        $quotedKeys = [];
        foreach ($fields as $field) {
            $quotedKeys[] = $this->quoteKey($field);
        }
        return $quotedKeys;
    }

    /**
     * 处理插入数据库的字符串值，防注入, 使用了PDO提供的quote方法
     *
     * @param string $value
     * @return string
     * @throws DbException
     */
    public function quoteValue($value)
    {
        return $this->pdo->quote((string)$value);
    }

    /**
     * 处理一组插入数据库的字符串值，防注入, 使用了PDO提供的quote方法
     *
     * @param array $values
     * @return array
     * @throws DbException
     */
    public function quoteValues($values)
    {
        $quotedValues = [];
        foreach ($values as $value) {
            $quotedValues[] = $this->pdo->quote((string)$value);
        }
        return $quotedValues;
    }

    /**
     * 处理插入数据库的字符串值，防注入, 仅处理敏感字符，不加外层引号，
     * 与 quote 方法的区别可以理解为 quoteValue 比 escape 多了最外层的引号
     *
     * @param string $value
     * @return string
     */
    abstract function escape($value);

}
