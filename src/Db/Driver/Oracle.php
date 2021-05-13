<?php

namespace Be\F\Db\Driver;

use Be\F\Db\Driver;
use Be\F\Db\DbException;

/**
 * 数据库类
 */
class Oracle extends Driver
{

    public function __construct($name, $pdo = null)
    {
        $this->name = $name;
        $this->connection = new \Be\F\Db\Connection\Oracle($name, $pdo);
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
        $this->connection = $connection;
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
        $this->connection = $connection;
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
        $this->connection = $connection;
        while ($result = $statement->fetchObject()) {
            yield $result;
        }
        $statement->closeCursor();
        $connection->release();
    }

    /**
     * 插入一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象或对象数组，对象属性需要和该表字段一致
     * @return int 插入的主键ID
     * @throws DbException
     */
    public function insert($table, $object)
    {
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('插入的数据格式须为对象或数组');
        }

        $tableFields = $this->getTableFields($table);

        $fields = [];
        $placeholders = [];
        foreach ($vars as $k => $var) {

            if (!isset($tableFields[$k])) {
                throw new DbException('字段 ' . $k . ' 在表 ' . $table . ' 中不存在！');
            }

            $fields[] = $this->connection->quoteKey($k);

            $tableField = $tableFields[$k];
            switch ($tableField['type']) {
                case 'date':
                    $placeholders[] = 'to_date(?, \'yyyy-mm-dd hh24:mi:ss\')';
                    break;
                case 'timestamp':
                    $placeholders[] = 'to_timestamp(?, \'yyyy-mm-dd hh24:mi:ss\')';
                    break;
                default:
                    $placeholders[] = '?';
            }
        }

        $sql = 'INSERT INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', $placeholders) . ')';

        $values = [];
        foreach ($vars as $k => $value) {
            $tableField = $tableFields[$k];
            switch ($tableField['type']) {
                case 'date':
                    $t = strtotime($value);
                    if (!$t) {
                        $values[] = '';
                    } else {
                        $values[] = date('Y-m-d H:i:s', $t);
                    }
                    break;
                case 'timestamp':
                    $t = strtotime($value);
                    if (!$t) {
                        $values[] = '';
                    } else {
                        $values[] = date('Y-m-d H:i:s', $t);
                    }
                    break;
                case 'number':
                    if (strpos($value, '.') === false) {
                        if (is_numeric($value)) {
                            $values[] = $value;
                        } else {
                            $values[] = 0;
                        }
                    } else {
                        $values[] = round($value, $tableField['scale']);
                    }
                    break;
                default:
                    $values[] = $value;
            }
        }

        $statement = $this->connection->execute($sql, array_values($vars));
        $statement->closeCursor();
        return 1;
    }

    /**
     * 批量插入多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要插入数据库的对象数组，对象属性需要和该表字段一致
     * @return array 批量插入的ID列表
     * @throws DbException
     */
    public function insertMany($table, $objects)
    {
        if (!is_array($objects) || count($objects) == 0) return [];

        $ids = [];
        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('批量插入的数据格式须为对象或数组');
        }
        ksort($vars);

        $tableFields = $this->getTableFields($table);

        $fields = [];
        $placeholders = [];
        foreach ($vars as $k => $var) {

            if (!isset($tableFields[$k])) {
                throw new DbException('字段 ' . $k . ' 在表 ' . $table . ' 中不存在！');
            }

            $fields[] = $this->connection->quoteKey($k);

            $tableField = $tableFields[$k];
            switch ($tableField['type']) {
                case 'date':
                    $placeholders[] = 'to_date(?, \'yyyy-mm-dd hh24:mi:ss\')';
                    break;
                case 'timestamp':
                    $placeholders[] = 'to_timestamp(?, \'yyyy-mm-dd hh24:mi:ss\')';
                    break;
                default:
                    $placeholders[] = '?';
            }
        }

        $sql = 'INSERT INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', $placeholders) . ')';
        $statement = $this->prepare($sql);
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('批量插入的数据格式须为对象或数组');
            }
            ksort($vars);

            $values = [];
            foreach ($vars as $k => $value) {

                if (!isset($tableFields[$k])) {
                    throw new DbException('字段 ' . $k . ' 在表 ' . $table . ' 中不存在！');
                }

                $tableField = $tableFields[$k];
                switch ($tableField['type']) {
                    case 'date':
                        $t = strtotime($value);
                        if (!$t) {
                            $values[] = '';
                        } else {
                            $values[] = date('Y-m-d H:i:s', $t);
                        }
                        break;
                    case 'timestamp':
                        $t = strtotime($value);
                        if (!$t) {
                            $values[] = '';
                        } else {
                            $values[] = date('Y-m-d H:i:s', $t);
                        }
                        break;
                    case 'number':
                        if (strpos($value, '.') === false) {
                            if (is_numeric($value)) {
                                $values[] = $value;
                            } else {
                                $values[] = 0;
                            }
                        } else {
                            $values[] = round($value, $tableField['scale']);
                        }
                        break;
                    default:
                        $values[] = $value;
                }
            }

            $result = $statement->execute($values);
            $ids[] = $result ? 1 : 0;
        }
        $statement->closeCursor();

        return $ids;
    }

    /**
     * 快速插入一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @return int 插入的主键ID
     * @throws DbException
     */
    public function quickInsert($table, $object)
    {
        $effectLines = null;

        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('快速插入的数据格式须为对象或数组');
        }

        $tableFields = $this->getTableFields($table);

        $fields = [];
        foreach ($vars as $k => $var) {

            if (!isset($tableFields[$k])) {
                throw new DbException('字段 ' . $k . ' 在表 ' . $table . ' 中不存在！');
            }

            $fields[] = $this->connection->quoteKey($k);
        }

        $sql = 'INSERT INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';

        $values = [];
        foreach ($vars as $k => $value) {

            $tableField = $tableFields[$k];

            switch ($tableField['type']) {
                case 'date':
                    $t = strtotime($value);
                    if (!$t) {
                        $values[] = '\'\'';
                    } else {
                        $values[] = 'to_date(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                    }
                    break;
                case 'timestamp':
                    $t = strtotime($value);
                    if (!$t) {
                        $values[] = '\'\'';
                    } else {
                        $values[] = 'to_timestamp(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                    }
                    break;
                case 'number':
                    if (strpos($value, '.') === false) {
                        if (is_numeric($value)) {
                            $values[] = $value;
                        } else {
                            $values[] = 0;
                        }
                    } else {
                        $values[] = round($value, $tableField['scale']);
                    }
                    break;
                default:
                    $values[] = $this->connection->quoteValue($value);
            }
        }

        $sql .= '(' . implode(',', $values) . ')';

        $statement = $this->connection->execute($sql);
        $statement->closeCursor();

        return 1;
    }

    /**
     * 快速批量插入多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要插入数据库的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickInsertMany($table, $objects)
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
            throw new DbException('快速批量插入的数据格式须为对象或数组');
        }
        ksort($vars);

        $tableFields = $this->getTableFields($table);

        $fields = [];
        foreach ($vars as $k => $var) {

            if (!isset($tableFields[$k])) {
                throw new DbException('字段 ' . $k . ' 在表 ' . $table . ' 中不存在！');
            }

            $fields[] = $this->connection->quoteKey($k);
        }

        $sql = 'INSERT ALL ';
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('快速批量插入的数据格式须为对象或数组');
            }
            ksort($vars);

            $values = [];
            foreach ($vars as $k => $value) {

                $tableField = $tableFields[$k];

                switch ($tableField['type']) {
                    case 'date':
                        $t = strtotime($value);
                        if (!$t) {
                            $values[] = '\'\'';
                        } else {
                            $values[] = 'to_date(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                        }
                        break;
                    case 'timestamp':
                        $t = strtotime($value);
                        if (!$t) {
                            $values[] = '\'\'';
                        } else {
                            $values[] = 'to_timestamp(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                        }
                        break;
                    case 'number':
                        if (strpos($value, '.') === false) {
                            if (is_numeric($value)) {
                                $values[] = $value;
                            } else {
                                $values[] = 0;
                            }
                        } else {
                            $values[] = round($value, $tableField['scale']);
                        }
                        break;
                    default:
                        $values[] = $this->connection->quoteValue($value);
                }
            }

            $sql .= 'INTO ' . $this->connection->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ') ';
        }

        $sql .= 'SELECT 1 FROM DUAL';
        $statement = $this->connection->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }



    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function update($table, $object, $primaryKey = null)
    {
        $tableFields = $this->getTableFields($table);

        $fields = [];
        $fieldValues = [];

        $where = [];
        $whereValue = [];

        if ($primaryKey === null) {
            $primaryKey = $this->getTablePrimaryKey($table);
            if ($primaryKey === null) {
                throw new DbException('新数据表' . $table . '无主键，不支持按主键更新！');
            }
        }

        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('更新的数据格式须为对象或数组');
        }

        foreach ($vars as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (!isset($tableFields[$key])) {
                throw new DbException('字段 ' . $key . ' 在表 ' . $table . ' 中不存在！');
            }

            $tableField = $tableFields[$key];

            $isPrimaryKey = false;
            if (is_array($primaryKey)) {
                if (in_array($key, $primaryKey)) {
                    $isPrimaryKey = true;
                }
            } else {
                if ($key == $primaryKey) {
                    $isPrimaryKey = true;
                }
            }

            // 主键作为WHERE条件，不更新
            if ($isPrimaryKey) {
                switch ($tableField['type']) {
                    case 'date':
                        $t = strtotime($value);
                        if (!$t) {
                            $where[] = $this->connection->quoteKey($key) . ' IS NULL';
                        } else {
                            $where[] = $this->connection->quoteKey($key) . ' = to_date(?, \'yyyy-mm-dd hh24:mi:ss\')';
                            $whereValue[] = date('Y-m-d H:i:s', $t);
                        }
                        break;
                    case 'timestamp':
                        $t = strtotime($value);
                        if (!$t) {
                            $where[] = $this->connection->quoteKey($key) . ' IS NULL';
                        } else {
                            $where[] = $this->connection->quoteKey($key) . ' = to_timestamp(?, \'yyyy-mm-dd hh24:mi:ss\')';
                            $whereValue[] = date('Y-m-d H:i:s', $t);
                        }
                        break;
                    case 'number':
                        $where[] = $this->connection->quoteKey($key) . '=?';
                        if (strpos($value, '.') === false) {
                            if (is_numeric($value)) {
                                $whereValue[] = $value;
                            } else {
                                $whereValue[] = 0;
                            }
                        } else {
                            $whereValue[] = round($value, $tableField['scale']);
                        }
                        break;
                    default:
                        if ($value == '') {
                            $where[] = $this->connection->quoteKey($key) . ' IS NULL';
                        } else {
                            $where[] = $this->connection->quoteKey($key) . '=?';
                            $whereValue[] = $value;
                        }
                }

                continue;
            }

            switch ($tableField['type']) {
                case 'date':

                    $t = strtotime($value);
                    if (!$t) {
                        $fields[] = $this->connection->quoteKey($key) . ' = ?';
                        $fieldValues[] = '';
                    } else {
                        $fields[] = $this->connection->quoteKey($key) . ' = to_date(?, \'yyyy-mm-dd hh24:mi:ss\')';
                        $fieldValues[] = date('Y-m-d H:i:s', $t);
                    }

                    break;
                case 'timestamp':

                    $t = strtotime($value);
                    if (!$t) {
                        $fields[] = $this->connection->quoteKey($key) . ' = ?';
                        $fieldValues[] = '';
                    } else {
                        $fields[] = $this->connection->quoteKey($key) . ' = to_timestamp(?, \'yyyy-mm-dd hh24:mi:ss\')';
                        $fieldValues[] = date('Y-m-d H:i:s', $t);
                    }

                    break;
                case 'number':
                    $fields[] = $this->connection->quoteKey($key) . '=?';
                    if (strpos($value, '.') === false) {
                        if (is_numeric($value)) {
                            $whereValue[] = $value;
                        } else {
                            $whereValue[] = 0;
                        }
                    } else {
                        $whereValue[] = round($value, $tableField['scale']);
                    }
                    break;
                default:
                    $fields[] = $this->connection->quoteKey($key) . '=?';
                    $fieldValues[] = $value;
            }
        }

        if ($where == null) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->connection->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $fieldValues = array_merge($fieldValues, $whereValue);

        $statement = $this->connection->execute($sql, $fieldValues);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 快速更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickUpdate($table, $object, $primaryKey = null)
    {
        $tableFields = $this->getTableFields($table);

        $where = [];
        $fields = [];

        if ($primaryKey === null) {
            $primaryKey = $this->getTablePrimaryKey($table);
            if ($primaryKey === null) {
                throw new DbException('新数据表' . $table . '无主键，不支持按主键更新！');
            }
        }

        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('更新的数据格式须为对象或数组');
        }

        foreach ($vars as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (!isset($tableFields[$key])) {
                throw new DbException('字段 ' . $key . ' 在表 ' . $table . ' 中不存在！');
            }

            $tableField = $tableFields[$key];

            $isPrimaryKey = false;
            if (is_array($primaryKey)) {
                if (in_array($key, $primaryKey)) {
                    $isPrimaryKey = true;
                }
            } else {
                if ($key == $primaryKey) {
                    $isPrimaryKey = true;
                }
            }

            // 主键作为WHERE条件，不更新
            if ($isPrimaryKey) {
                switch ($tableField['type']) {
                    case 'date':
                        $t = strtotime($value);
                        if (!$t) {
                            $where[] = $this->connection->quoteKey($key) . ' IS NULL';
                        } else {
                            $where[] = $this->connection->quoteKey($key) . ' = to_date(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                        }
                        break;
                    case 'timestamp':
                        $t = strtotime($value);
                        if (!$t) {
                            $where[] = $this->connection->quoteKey($key) . ' IS NULL';
                        } else {
                            $where[] = $this->connection->quoteKey($key) . ' = to_timestamp(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                        }
                        break;
                    case 'number':
                        if (strpos($value, '.') === false) {
                            if (is_numeric($value)) {
                                $where[] = $this->connection->quoteKey($key) . '=' . $value;
                            } else {
                                $where[] = $this->connection->quoteKey($key) . '=0';
                            }
                        } else {
                            $where[] = $this->connection->quoteKey($key) . '=' . round($value, $tableField['scale']);
                        }
                        break;
                    default:
                        if ($value == '') {
                            $where[] = $this->connection->quoteKey($key) . ' IS NULL';
                        } else {
                            $where[] = $this->connection->quoteKey($key) . '=' . $this->connection->quoteValue($value);
                        }
                }

                continue;
            }

            switch ($tableField['type']) {
                case 'date':

                    $t = strtotime($value);
                    if (!$t) {
                        $fields[] = $this->connection->quoteKey($key) . ' = \'\'';
                    } else {
                        $fields[] = $this->connection->quoteKey($key) . ' = to_date(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                    }

                    break;
                case 'timestamp':

                    $t = strtotime($value);
                    if (!$t) {
                        $fields[] = $this->connection->quoteKey($key) . ' = \'\'';
                    } else {
                        $fields[] = $this->connection->quoteKey($key) . ' = to_timestamp(\'' . date('Y-m-d H:i:s', $t) . '\', \'yyyy-mm-dd hh24:mi:ss\')';
                    }

                    break;
                case 'number':
                    if (strpos($value, '.') === false) {
                        if (is_numeric($value)) {
                            $fields[] = $this->connection->quoteKey($key) . '=' . $value;
                        } else {
                            $fields[] = $this->connection->quoteKey($key) . '=0';
                        }
                    } else {
                        $fields[] = $this->connection->quoteKey($key) . '=' . round($value, $tableField['scale']);
                    }
                    break;
                default:
                    $fields[] = $this->connection->quoteKey($key) . '=' . $this->connection->quoteValue($value);
            }

        }

        if ($where == null) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->connection->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $statement = $this->connection->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects $object 要更新的对象数组，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键或指定键名更新，未指定时自动取表的主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function updateMany($table, $objects, $primaryKey = null)
    {
        return 0;
    }

    /**
     * 快速批量更新多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要快速批量更新的对象数组，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键或指定键名更新，未指定时自动取表的主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickUpdateMany($table, $objects, $primaryKey = null)
    {
        return 0;
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
        throw new DbException('Oracle 数据库不支持 Replace Into！');
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
        throw new DbException('Oracle 数据库不支持 Replace Into！');
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
        throw new DbException('Oracle 数据库不支持 Replace Into！');
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
        throw new DbException('Oracle 数据库不支持 Replace Into！');
    }

    /**
     * 获取当前数据库所有表信息
     *
     * @return array
     */
    public function getTables()
    {
        return $this->getObjects('SELECT * FROM "USER_TAB_COMMENTS"');
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public function getTableNames()
    {
        return $this->getValues('SELECT "TABLE_NAME" FROM "USER_TAB_COMMENTS"');
    }

    /**
     * 获取当前数据库所有表信息
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->getObjects('SELECT * FROM "ALL_TAB_COMMENTS"');
    }

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public function getAllTableNames()
    {
        return $this->getValues('SELECT "TABLE_NAME" FROM "ALL_TAB_COMMENTS"');
    }

    /**
     * 获取当前连接的所有库信息
     *
     * @return array
     */
    public function getDatabases()
    {
        return $this->getObjects('SELECT * FROM v$database');
    }

    /**
     * 获取当前连接的所有库名
     *
     * @return array
     */
    public function getDatabaseNames()
    {
        return $this->getValues('SELECT "name" FROM v$database');
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

        $sql = null;
        if (strpos($table, '.')) {
            $tables = explode('.', $table);
            $owner = $tables[0];
            $table = $tables[1];

            $sql = 'SELECT 
                      a."COLUMN_NAME",
                      a."DATA_TYPE",
                      a."DATA_LENGTH",
                      a."DATA_PRECISION",
                      a."DATA_SCALE",
                      a."DATA_DEFAULT",
                      a."NULLABLE",
                      b."COMMENTS"
                    FROM "ALL_TAB_COLUMNS" a
                    INNER JOIN "ALL_COL_COMMENTS" b 
                      ON b."COLUMN_NAME" = a."COLUMN_NAME" 
                      AND b."TABLE_NAME" = a."TABLE_NAME"
                      AND b."OWNER" = ' . $this->connection->quoteValue($owner) . ' 
                      AND b."TABLE_NAME" = ' . $this->connection->quoteValue($table) . ' 
                    WHERE a."OWNER" = ' . $this->connection->quoteValue($owner) . ' 
                    AND a."TABLE_NAME" = ' . $this->connection->quoteValue($table);
        } else {
            $sql = 'SELECT 
                      a."COLUMN_NAME",
                      a."DATA_TYPE",
                      a."DATA_LENGTH",
                      a."DATA_PRECISION",
                      a."DATA_SCALE",
                      a."DATA_DEFAULT",
                      a."NULLABLE",
                      b."COMMENTS"
                    FROM "USER_TAB_COLUMNS" a
                    INNER JOIN "USER_COL_COMMENTS" b 
                      ON b."COLUMN_NAME" = a."COLUMN_NAME" 
                      AND b."TABLE_NAME" = a."TABLE_NAME"
                    WHERE a."TABLE_NAME" = ' . $this->connection->quoteValue($table);
        }

        $fields = $this->getObjects($sql);

        $data = [];
        foreach ($fields as $field) {

            $data[$field->COLUMN_NAME] = [
                'name' => $field->COLUMN_NAME,
                'type' => strtolower($field->DATA_TYPE),
                'length' => $field->DATA_LENGTH,
                'precision' => $field->DATA_PRECISION,
                'scale' => $field->DATA_SCALE,
                'comment' => $field->COMMENTS,
                'default' => $field->DATA_DEFAULT,
                'nullAble' => $field->NULLABLE == 'Y' ? true : false,
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

        $primaryKeys = [];
        if (strpos($table, '.')) {

            $tables = explode('.', $table);
            $owner = $tables[0];
            $table = $tables[1];

            $sql = 'SELECT "CONSTRAINT_NAME" 
                    FROM "ALL_CONSTRAINTS" 
                    WHERE "OWNER" = '.$this->connection->quoteValue($owner).' 
                    AND "TABLE_NAME" = ' . $this->connection->quoteValue($table) . '
                    AND "CONSTRAINT_TYPE" =\'P\'';

            $constraintName = $this->getValue($sql);

            if ($constraintName) {
                $sql = 'SELECT "COLUMN_NAME" 
                        FROM "ALL_COL_COMMENTS" 
                        WHERE "CONSTRAINT_NAME" = ' . $this->connection->quoteValue($constraintName) ;

                $primaryKeys = $this->getValues($sql);
            }

        } else {

            $sql = 'SELECT "CONSTRAINT_NAME" 
                    FROM "USER_CONSTRAINTS" 
                    WHERE "TABLE_NAME" = ' . $this->connection->quoteValue($table) . '
                    AND "CONSTRAINT_TYPE" =\'P\'';

            $constraintName = $this->getValue($sql);

            if ($constraintName) {
                $sql = 'SELECT "COLUMN_NAME" 
                        FROM "USER_CONS_COLUMNS" 
                        WHERE "CONSTRAINT_NAME" = ' . $this->connection->quoteValue($constraintName) ;

                $primaryKeys = $this->getValues($sql);
            }
        }

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
        $statement = $this->connection->execute('DROP TABLE ' . $this->connection->quoteKey($table));
        $statement->closeCursor();
    }


}