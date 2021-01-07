<?php

namespace Be\Framework\db\Driver;

use Be\Framework\Db\Driver;
use Be\Framework\Db\DbException;

/**
 * 数据库类 MSSQL(SQL Server)
 */
class Mssql extends Driver
{

    /**
     * 连接数据库
     *
     * @return \PDO 连接
     * @throws DbException
     */
    public function connect()
    {
        if ($this->connection === null) {
            $config = $this->config;

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

            $connection = new \PDO($dsn, $config['username'], $config['password'], $options);
            if (!$connection) throw new DbException('连接MSSQL数据库' . $config['name'] . '（' . $config['host'] . '） 失败！');

            $this->connection = $connection;
        }

        return $this->connection;
    }

    /**
     * 返回一个跌代器数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return \Generator
     */
    public function getYieldValues($sql, array $bind = null)
    {
        $connection = $this->connection;
        $this->connection = null;
        $this->connect();
        $statement = $this->execute($sql, $bind);
        $this->connection = $connection;
        while ($tuple = $statement->fetch(\PDO::FETCH_NUM)) {
            yield $tuple[0];
        }
        $statement->closeCursor();
        $connection = null;
    }

    /**
     * 返回一个跌代器二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return \Generator
     */
    public function getYieldArrays($sql, array $bind = null)
    {
        $connection = $this->connection;
        $this->connection = null;
        $this->connect();
        $statement = $this->execute($sql, $bind);
        $this->connection = $connection;
        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $result;
        }
        $statement->closeCursor();
        $connection = null;
    }

    /**
     * 返回一个跌代器对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return \Generator
     */
    public function getYieldObjects($sql, array $bind = null)
    {
        $connection = $this->connection;
        $this->connection = null;
        $this->connect();
        $statement = $this->execute($sql, $bind);
        $this->connection = $connection;
        while ($result = $statement->fetchObject()) {
            yield $result;
        }
        $statement->closeCursor();
        $connection = null;
    }

    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要更新的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function replace($table, $object)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要更新的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function replaceMany($table, $objects)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 快速更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要更新的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickReplace($table, $object)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 快速批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要更新的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickReplaceMany($table, $objects)
    {
        throw new DbException('Mssql 数据库不支持 Replace Into！');
    }

    /**
     * 获取 insert 插入后产生的 id
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return (int)$this->getValue('SELECT ISNULL(SCOPE_IDENTITY(), 0)');
    }

    /**
     * 获取当前数据库所有表信息
     *
     * @return array
     */
    public function getTables()
    {
        // SELECT * FROM sysobjects WHERE xType='u';
        // SELECT * FROM sys.objects WHERE type='U';
        // SELECT * FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = 'BASE TABLE';
        $sql = 'SELECT 
                    a.name, 
                    g.value AS comment
                FROM sys.tables a
                LEFT JOIN sys.extended_properties g ON a.object_id = g.major_id AND g.minor_id = 0
                WHERE a.type=\'U\'';
        return $this->getObjects($sql);
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public function getTableNames()
    {
        // SELECT [TABLE_NAME] FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = 'BASE TABLE'
        return $this->getValues('SELECT [name] FROM sys.tables WHERE [type] = \'U\'');
    }

    /**
     * 获取当前连接的所有库信息
     *
     * @return array
     */
    public function getDatabases()
    {
        return $this->getObjects('SELECT * FROM master..sysdatabasesWHERE [name]!=\'master\'');
    }

    /**
     * 获取当前连接的所有库名
     *
     * @return array
     */
    public function getDatabaseNames()
    {
        return $this->getValues('SELECT [name] FROM master..sysdatabasesWHERE [name]!=\'master\'');
    }

    /**
     * 获取一个表的字段列表
     *
     * @param string $table 表名
     * @return array 对象数组
     * 字段对象典型结构
     * {
     *      'name' => '字段名',
     *      'type' => '类型',
     *      'length' => '长度',
     *      'precision' => '精度',
     *      'scale' => '长度',
     *      'comment' => '备注',
     *      'default' => '默认值',
     *      'nullAble' => '是否允许为空',
     * }
     */
    public function getTableFields($table)
    {
        $cacheKey = 'TableFields:' . $table;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sql = 'SELECT  
                    a.name,  
                    ISNULL(d.[value], \'\') AS [comment],  
                    b.name AS [type],  
                    a.length AS [length],  
                    ISNULL(COLUMNPROPERTY(a.id, a.name, \'Scale\'), 0) AS [scale],  
                    a.isnullable AS [null_able],
                    c.text AS [default]
                FROM syscolumns a  
                LEFT JOIN systypes b ON a.xtype = b.xusertype  
                LEFT JOIN syscomments c ON a.cdefault = c.id  
                LEFT JOIN sys.extended_properties d ON a.id = d.major_id AND a.colid = d.minor_id AND d.name = \'MS_Description\'  
                WHERE a.id=object_id(\'' . $table . '\')';
        $fields = $this->getObjects($sql);

        $data = [];
        foreach ($fields as $field) {
            $data[$field->name] = [
                'name' => $field->name,
                'type' => $field->type,
                'length' => $field->length,
                'precision' => 0,
                'scale' => $field->scale,
                'comment' => $field->comment,
                'default' => $field->default,
                'nullAble' => $field->null_able ? true : false,
            ];
        }

        $this->cache[$cacheKey] = $data;
        return $data;
    }

    /**
     * 获取指定表的主银
     *
     * @param string $table 表名
     * @return string | array | null
     */
    public function getTablePrimaryKey($table)
    {
        $cacheKey = 'TablePrimaryKey:' . $table;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sql = 'SELECT COL_NAME(a.parent_obj, c.colid) 
                FROM sysobjects a
                LEFT JOIN sysindexes b ON a.name = b.name
                LEFT JOIN sysindexkeys c ON b.id = c.id AND b.indid = c.indid
                WHERE a.xtype=\'PK\' AND a.parent_obj=OBJECT_ID(\'' . $table . '\')';
        $primaryKeys = $this->getValues($sql);

        $primaryKey = null;
        $count = count($primaryKeys);
        if ($count > 1) {
            $primaryKey = $primaryKeys;
        } elseif ($count == 1) {
            $primaryKey = $primaryKeys[0];
        }

        $this->cache[$cacheKey] = $primaryKey;
        return $primaryKey;
    }

    /**
     * 删除表
     *
     * @param string $table 表名
     */
    public function dropTable($table)
    {
        $statement = $this->execute('DROP TABLE ' . $this->quoteKey($table));
        $statement->closeCursor();
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
