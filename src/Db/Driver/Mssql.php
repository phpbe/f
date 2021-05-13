<?php

namespace Be\F\Db\Driver;

use Be\F\Db\Driver;
use Be\F\Db\DbException;

/**
 * 数据库类 MSSQL(SQL Server)
 */
class Mssql extends Driver
{


    public function __construct($name, $pdo = null)
    {
        $this->name = $name;
        $this->connection = new \Be\F\Db\Connection\Mssql($name, $pdo);
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
        $connection = new \Be\F\Db\Connection\Mssql($this->name);
        $statement = $connection->execute($sql, $bind);
        while ($tuple = $statement->fetch(\PDO::FETCH_NUM)) {
            yield $tuple[0];
        }
        $statement->closeCursor();
        $connection->release();
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
        $connection = new \Be\F\Db\Connection\Mssql($this->name);
        $statement = $connection->execute($sql, $bind);
        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $result;
        }
        $statement->closeCursor();
        $connection->release();
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
        $connection = new \Be\F\Db\Connection\Mssql($this->name);
        $statement = $connection->execute($sql, $bind);
        while ($result = $statement->fetchObject()) {
            yield $result;
        }
        $statement->closeCursor();
        $connection->release();
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
        $statement = $this->connection->execute('DROP TABLE ' . $this->quoteKey($table));
        $statement->closeCursor();
    }


}
