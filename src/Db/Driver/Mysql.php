<?php

namespace Be\F\Db\Driver;

use Be\F\Db\Driver;
use Be\F\Db\DbException;

/**
 * 数据库类
 */
class Mysql extends Driver
{

    public function __construct($name, $pdo = null)
    {
        $this->name = $name;
        $this->connection = new \Be\F\Db\Connection\Mysql($name, $pdo);
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
        $connection = new \Be\F\Db\Connection\Mysql($this->name);
        $connection->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
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
        $connection = new \Be\F\Db\Connection\Mysql($this->name);
        $connection->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
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
        $connection = new \Be\F\Db\Connection\Mysql($this->name);
        $connection->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $statement = $connection->execute($sql, $bind);
        while ($result = $statement->fetchObject()) {
            yield $result;
        }
        $statement->closeCursor();
        $connection->release();
    }

    /**
     * 替换一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要替换的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function replace($table, $object)
    {
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('替换的数据格式须为对象或数组');
        }

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->connection->quoteKey($field);
        }

        $sql = 'REPLACE INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
        $statement = $this->connection->execute($sql, array_values($vars));
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 批量替换多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要替换的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function replaceMany($table, $objects)
    {
        if (!is_array($objects) || count($objects) == 0) return 0;

        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('批量替换的数据格式须为对象或数组');
        }
        ksort($vars);

        $effectLines = 0;

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->connection->quoteKey($field);
        }

        $sql = 'REPLACE INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
        $statement = $this->prepare($sql);
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('批量替换的数据格式须为对象或数组');
            }
            ksort($vars);
            $statement->execute(array_values($vars));
            $effectLines += $statement->rowCount();
        }
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 快速替换一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要替换的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickReplace($table, $object)
    {
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('快速替换的数据格式须为对象或数组');
        }

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->connection->quoteKey($field);
        }

        $sql = 'REPLACE INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';
        $values = array_values($vars);
        foreach ($values as &$value) {
            if ($value !== null) {
                $value = $this->connection->quoteValue($value);
            } else {
                $value = 'null';
            }
        }
        $sql .= '(' . implode(',', $values) . ')';
        $statement = $this->connection->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 快速批量替换多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要替换的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickReplaceMany($table, $objects)
    {
        if (!is_array($objects) || count($objects) == 0) return 0;

        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('快速批量替换的数据格式须为对象或数组');
        }
        ksort($vars);

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->connection->quoteKey($field);
        }

        $sql = 'REPLACE INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('快速批量替换的数据格式须为对象或数组');
            }
            ksort($vars);
            $values = array_values($vars);
            foreach ($values as &$value) {
                if ($value !== null) {
                    $value = $this->connection->quoteValue($value);
                } else {
                    $value = 'null';
                }
            }
            $sql .= '(' . implode(',', $values) . '),';
        }
        $sql = substr($sql, 0, -1);
        $statement = $this->connection->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 获取当前数据库所有表信息
     *
     * @return array
     */
    public function getTables()
    {
        return $this->getObjects('SHOW TABLE STATUS');
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public function getTableNames()
    {
        return $this->getValues('SHOW TABLES');
    }

    /**
     * 获取当前连接的所有库信息
     *
     * @return array
     */
    public function getDatabases()
    {
        return $this->getObjects('SELECT * FROM information_schema.SCHEMATA WHERE `SCHEMA_NAME`!=\'information_schema\'');
    }

    /**
     * 获取当前连接的所有库名
     *
     * @return array
     */
    public function getDatabaseNames()
    {
        return $this->getValues('SELECT `SCHEMA_NAME` FROM information_schema.SCHEMATA WHERE `SCHEMA_NAME`!=\'information_schema\'');
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

        $fields = $this->getObjects('SHOW FULL FIELDS FROM ' . $this->connection->quoteKey($table));

        $data = [];
        foreach ($fields as $field) {
            $type = '';
            $length = '';
            $precision = '';
            $scale = '';

            $pos = strpos($field->Type, '(');
            if ($pos !== false) {
                $type = strtolower(substr($field->Type, 0, $pos));
                switch ($type) {
                    case 'int':
                    case 'tinyint':
                    case 'smallint':
                    case 'mediumint':
                    case 'bigint':
                    case 'bit':

                    case 'char':
                    case 'varchar':

                    case 'year':
                        $length = substr($field->Type, $pos + 1, strpos($field->Type, ')') - $pos - 1);
                        break;
                    case 'decimal':

                        $str = substr($field->Type, $pos + 1, strpos($field->Type, ')') - $pos - 1);
                        $tmpPos = strpos($str, ',');
                        if ($tmpPos === false) {
                            $precision = $str;
                            $scale = 0;
                        } else {
                            $precision = substr($str, 0, $tmpPos);
                            $scale = substr($str, $tmpPos + 1);
                        }

                        break;
                }
            } else {
                $pos = strpos($field->Type, ' ');
                if ($pos !== false) {
                    $type = strtolower(substr($field->Type, 0, $pos));
                } else {
                    $type = strtolower($field->Type);
                }
            }

            $unsigned = false;
            if (in_array($type, [
                'int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal'
            ])) {
                if (strpos($field->Type, 'unsigned') !== false) {
                    $unsigned = true;
                }
            }

            $data[$field->Field] = [
                'name' => $field->Field,
                'type' => $type,
                'length' => $length,
                'precision' => $precision,
                'scale' => $scale,
                'comment' => $field->Comment,
                'default' => $field->Default,
                'nullAble' => $field->Null == 'YES' ? true : false,

                'unsigned' => $unsigned,
                'collation' => $field->Collation,
                'key' => $field->Key,
                'extra' => $field->Extra,
                'privileges' => $field->Privileges,
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
        $tableFields = $this->getTableFields($table);

        $primaryKeys = [];
        foreach ($tableFields as $tableField) {
            if ($tableField['key'] == 'PRI') {
                $primaryKeys[] = $tableField['name'];
            }
        }

        $count = count($primaryKeys);
        if ($count > 1) {
            return $primaryKeys;
        } elseif ($count == 1) {
            return $primaryKeys[0];
        }

        return null;
    }

    /**
     * 删除表
     *
     * @param string $table 表名
     */
    public function dropTable($table)
    {
        $statement = $this->connection->execute('DROP TABLE IF EXISTS ' . $this->connection->quoteKey($table));
        $statement->closeCursor();
    }


}
