<?php

namespace Be\Framework\db;

use Be\Framework\CacheProxy;
use Be\Framework\Exception\DbException;

/**
 * 数据库类
 */
abstract class Driver
{
    /**
     * @var \PDO
     */
    protected $connection = null; // 数据库连接

    /**
     * @var \PDOStatement
     */
    protected $statement = null; // 预编译 sql

    protected $config = [];

    protected $transactions = 0; // 开启的事务数，防止嵌套

    protected $cache = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 启动缓存代理
     *
     * @param int $expire 超时时间
     * @return CacheProxy | Driver
     */
    public function withCache($expire = 600)
    {
        return new CacheProxy($this, $expire);
    }

    /**
     * 连接数据库
     *
     * @return \PDO 连接
     * @throws DbException
     */
    public function connect()
    {
        return $this->connection;
    }

    /**
     * 关闭数据库连接
     *
     * @return bool 是否关闭成功
     */
    public function close()
    {
        if ($this->connection) $this->connection = null;
        return true;
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
        if ($this->connection === null) $this->connect();

        $statement = null;
        if ($options === null) {
            $statement = $this->connection->prepare($sql);
        } else {
            $statement = $this->connection->prepare($sql, $options);
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
            if ($this->connection === null) $this->connect();
            $statement = $this->connection->query($sql);
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
     * @return bool
     * @throws DbException | \PDOException | \Exception
     */
    public function query($sql, array $bind = null, array $prepareOptions = null)
    {
        $this->execute($sql, $bind, $prepareOptions)->closeCursor();
        return true;
    }

    /**
     * 返回单一查询结果, 多行多列记录时, 只返回第一行第一列
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return string
     * @throws DbException
     */
    public function getValue($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $tuple = $statement->fetch(\PDO::FETCH_NUM);
        $statement->closeCursor();
        if ($tuple === false) return false;
        return $tuple[0];
    }

    /**
     * 返回查询单列结果的数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     * @throws DbException
     */
    public function getValues($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $values = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $statement->closeCursor();
        return $values;
    }

    /**
     * 返回一个跌代器数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return \Generator
     */
    abstract public function getYieldValues($sql, array $bind = null);

    /**
     * 返回键值对数组
     * 查询两个或两个以上字段，第一列字段作为 key, 乘二列字段作为 value，多于两个字段时忽略
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     * @throws DbException
     */
    public function getKeyValues($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $keyValues = $statement->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
        $statement->closeCursor();
        return $keyValues;
    }

    /**
     * 返回一个数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     * @throws DbException
     */
    public function getArray($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $array = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $array;
    }

    /**
     * 返回一个二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array
     * @throws DbException
     */
    public function getArrays($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $arrays = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $arrays;
    }

    /**
     * 返回一个跌代器二维数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return \Generator
     */
    abstract public function getYieldArrays($sql, array $bind = null);

    /**
     * 返回一个带下标索引的二维数组
     *
     * @param string $sql 查询语句
     * @param null | array $bind 参数
     * @param null | string $key 作为下标索引的字段名
     * @return array
     * @throws DbException
     */
    public function getKeyArrays($sql, array $bind = null, $key = null)
    {
        $statement = $this->execute($sql, $bind);
        $arrays = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        if (count($arrays) == 0) return [];

        if ($key === null) {
            $key = key($arrays[0]);
        }

        $result = [];
        foreach ($arrays as $array) {
            $result[$array[$key]] = $array;
        }

        return $result;
    }

    /**
     * 返回一个数据库记录对象
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return object
     * @throws DbException
     */
    public function getObject($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $object = $statement->fetchObject();
        $statement->closeCursor();
        return $object;
    }

    /**
     * 返回一个对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return array(object)
     * @throws DbException
     */
    public function getObjects($sql, array $bind = null)
    {
        $statement = $this->execute($sql, $bind);
        $objects = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();
        return $objects;
    }

    /**
     * 返回一个跌代器对象数组
     *
     * @param string $sql 查询语句
     * @param array $bind 参数
     * @return \Generator
     */
    abstract public function getYieldObjects($sql, array $bind = null);

    /**
     * 返回一个带下标索引的对象数组
     *
     * @param string $sql 查询语句
     * @param null | array $bind 参数
     * @param null | string $key 作为下标索引的字段名
     * @return array(object)
     * @throws DbException
     */
    public function getKeyObjects($sql, array $bind = null, $key = null)
    {
        $statement = $this->execute($sql, $bind);
        $objects = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();

        if (count($objects) == 0) return [];

        if ($key === null) {
            $vars = get_object_vars($objects[0]);
            $key = key($vars);
        }

        $result = [];
        foreach ($objects as $object) {
            $result[$object->$key] = $object;
        }

        return $result;
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

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
        $statement = $this->execute($sql, array_values($vars));
        $statement->closeCursor();
        return $this->getLastInsertId();
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

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES(' . implode(',', array_fill(0, count($vars), '?')) . ')';
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
            $statement->execute(array_values($vars));
            $ids[] = $this->getLastInsertId();
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

        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';
        $values = array_values($vars);
        foreach ($values as &$value) {
            if ($value !== null) {
                $value = $this->quoteValue($value);
            } else {
                $value = 'null';
            }
        }
        $sql .= '(' . implode(',', $values) . ')';
        $statement = $this->execute($sql);
        $statement->closeCursor();

        return $this->getLastInsertId();
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


        $fields = [];
        foreach (array_keys($vars) as $field) {
            $fields[] = $this->quoteKey($field);
        }

        $sql = 'INSERT INTO ' . $this->quoteKey($table) . '(' . implode(',', $fields) . ') VALUES';
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
            $values = array_values($vars);
            foreach ($values as &$value) {
                if ($value !== null) {
                    $value = $this->quoteValue($value);
                } else {
                    $value = 'null';
                }
            }
            $sql .= '(' . implode(',', $values) . '),';
        }
        $sql = substr($sql, 0, -1);
        $statement = $this->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键或指定键名更新，未指定时自动取表的主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function update($table, $object, $primaryKey = null)
    {
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

            if (is_array($primaryKey)) {

                if (in_array($key, $primaryKey)) {
                    $where[] = $this->quoteKey($key) . '=?';
                    $whereValue[] = $value;
                    continue;
                }

            } else {

                // 主键不更新
                if ($key == $primaryKey) {
                    $where[] = $this->quoteKey($key) . '=?';
                    $whereValue[] = $value;
                    continue;
                }
            }

            $fields[] = $this->quoteKey($key) . '=?';
            $fieldValues[] = $value;
        }

        if (!$where) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $fieldValues = array_merge($fieldValues, $whereValue);

        $statement = $this->execute($sql, $fieldValues);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 快速更新一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要插入数据库的对象，对象属性需要和该表字段一致
     * @param null | string | array $primaryKey 主键或指定键名更新，未指定时自动取表的主键
     * @return int 影响的行数
     * @throws DbException
     */
    public function quickUpdate($table, $object, $primaryKey = null)
    {
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

            if (is_array($primaryKey)) {

                if (in_array($key, $primaryKey)) {
                    $where[] = $this->quoteKey($key) . '=' . $this->quoteValue($value);
                    continue;
                }

            } else {

                // 主键不更新
                if ($key == $primaryKey) {
                    $where[] = $this->quoteKey($key) . '=' . $this->quoteValue($value);
                    continue;
                }
            }

            if ($value === null) {
                $fields[] = $this->quoteKey($key) . '=null';
            } else {
                $fields[] = $this->quoteKey($key) . '=' . $this->quoteValue($value);
            }
        }

        if (!$where) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $statement = $this->execute($sql);
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
        if (!is_array($objects) || count($objects) == 0) return 0;

        if ($primaryKey === null) {
            $primaryKey = $this->getTablePrimaryKey($table);
            if ($primaryKey === null) {
                throw new DbException('新数据表' . $table . '无主键，不支持按主键更新！');
            }
        }

        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('批量更新的数据格式须为对象或数组');
        }
        ksort($vars);

        $fields = [];
        $where = [];
        foreach ($vars as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (is_array($primaryKey)) {
                if (in_array($key, $primaryKey)) {
                    $where[] = $this->quoteKey($key) . '=?';
                    continue;
                }
            } else {
                // 主键不更新
                if ($key == $primaryKey) {
                    $where[] = $this->quoteKey($key) . '=?';
                    continue;
                }
            }

            $fields[] = $this->quoteKey($key) . '=?';
        }

        if (!$where) {
            throw new DbException('更新数据时未指定条件！');
        }

        $sql = 'UPDATE ' . $this->quoteKey($table) . ' SET ' . implode(',', $fields) . ' WHERE ' . implode(' AND ', $where);
        $statement = $this->prepare($sql);

        $effectLines = 0;
        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('批量更新的数据格式须为对象或数组');
            }
            ksort($vars);

            $fieldValues = [];
            $whereValue = [];
            foreach ($vars as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }

                if (is_array($primaryKey)) {

                    if (in_array($key, $primaryKey)) {
                        $whereValue[] = $value;
                        continue;
                    }

                } else {

                    // 主键不更新
                    if ($key == $primaryKey) {
                        $whereValue[] = $value;
                        continue;
                    }
                }

                $fieldValues[] = $value;
            }

            if (count($whereValue) != count($where)) {
                throw new DbException('批量更新的数组未包含必须的主键名！');
            }

            if (count($fieldValues) != count($fields)) {
                throw new DbException('批量更新的数组内部结构不一致！');
            }

            $fieldValues = array_merge($fieldValues, $whereValue);
            $statement->execute(array_values($fieldValues));
            $effectLines += $statement->rowCount();
        }
        $statement->closeCursor();

        return $effectLines;
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
        if (!is_array($objects) || count($objects) == 0) return 0;

        if ($primaryKey === null) {
            $primaryKey = $this->getTablePrimaryKey($table);
            if ($primaryKey === null) {
                throw new DbException('新数据表' . $table . '无主键，不支持快速批量更新！');
            }
        }

        // 获取第一条记灵的结构作为更新的数据结构
        reset($objects);
        $object = current($objects);
        $vars = null;
        if (is_array($object)) {
            $vars = $object;
        } elseif (is_object($object)) {
            $vars = get_object_vars($object);
        } else {
            throw new DbException('快速批量更新的数据结构须为对象或数组');
        }
        ksort($vars);
        $fields = array_keys($vars);

        // 检查主键值是否存在
        if (is_array($primaryKey)) {
            foreach ($primaryKey as $pKey) {
                if (!in_array($pKey, $fields)) {
                    throw new DbException('快速批量更新的数据结构中未包念主键' . $pKey . '！');
                }
            }
        } else {
            if (!in_array($primaryKey, $fields)) {
                throw new DbException('快速批量更新的数据结构中未包念主键' . $primaryKey . '！');
            }
        }

        $sql = 'UPDATE ' . $this->quoteKey($table) . ' SET ';

        $primaryKeyIn = [];
        $caseMapping = [];
        foreach ($fields as $field) {
            if (is_array($primaryKey)) {
                if (in_array($field, $primaryKey)) {
                    continue;
                }
            } else {
                if ($field == $primaryKey) {
                    continue;
                }
            }

            $caseMapping[$field] = [];
        }

        foreach ($objects as $o) {
            $vars = null;
            if (is_array($o)) {
                $vars = $o;
            } elseif (is_object($o)) {
                $vars = get_object_vars($o);
            } else {
                throw new DbException('批量更新的数据结构须为对象或数组');
            }
            ksort($vars);

            foreach ($fields as $field) {
                if (!isset($vars[$field])) {
                    throw new DbException('批量更新的数据结构不一致');
                }

                if (is_array($primaryKey)) {
                    if (in_array($field, $primaryKey)) {
                        continue;
                    }

                    $when = [];
                    foreach ($primaryKey as $pKey) {
                        $when[] = $this->quoteKey($pKey) . '=' . $this->quoteValue($vars[$pKey]);
                    }

                    if ($vars[$field] === null) {
                        $caseMapping[$field][] = 'WHEN ' . implode(' AND ', $when) . ' THEN null';
                    } else {
                        $caseMapping[$field][] = 'WHEN ' . implode(' AND ', $when) . ' THEN ' . $this->quoteValue($vars[$field]);
                    }

                } else {
                    // 主键不更新
                    if ($field == $primaryKey) {
                        continue;
                    }

                    if ($vars[$field] !== null) {
                        $caseMapping[$field][] = 'WHEN ' . $this->quoteValue($vars[$primaryKey]) . ' THEN null';
                    } else {
                        $caseMapping[$field][] = 'WHEN ' . $this->quoteValue($vars[$primaryKey]) . ' THEN ' . $this->quoteValue($vars[$field]);
                    }
                }
            }

            if (is_array($primaryKey)) {
                $in = [];
                foreach ($primaryKey as $pKey) {
                    $in[] = $this->quoteValue($vars[$pKey]);
                }
                $primaryKeyIn[] = '('.implode(',', $in).')';
            } else {
                $primaryKeyIn[] = $this->quoteValue($vars[$primaryKey]);
            }
        }

        foreach ($caseMapping as $field => $cases) {
            $sql .= $this->quoteKey($field) . ' = CASE ';

            if (!is_array($primaryKey)) {
                $sql .= $this->quoteKey($primaryKey) . ' ';
            }

            $sql .= implode(' ', $cases);
            $sql .= 'END,';
        }

        $sql = substr($sql, 0, -1);
        $sql .= ' WHERE ';
        if (is_array($primaryKey)) {
            $sql .= '(' . implode(',', $this->quoteKeys($primaryKey)) . ') IN ';
        } else {
            $sql .= $this->quoteKey($primaryKey) . ' IN ';
        }

        $sql .= '('.implode(',', $primaryKeyIn).')';

        $statement = $this->execute($sql);
        $effectLines = $statement->rowCount();
        $statement->closeCursor();

        return $effectLines;
    }

    /**
     * 替换一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要替换的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public abstract function replace($table, $object);

    /**
     * 批量替换多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要替换的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public abstract function replaceMany($table, $objects);

    /**
     * 快速替换一个对象到数据库
     *
     * @param string $table 表名
     * @param array | object $object 要替换的对象，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public abstract function quickReplace($table, $object);

    /**
     * 快速批量替换多个对象到数据库
     *
     * @param string $table 表名
     * @param array $objects 要替换的对象数组，对象属性需要和该表字段一致
     * @return int 影响的行数
     * @throws DbException
     */
    public abstract function quickReplaceMany($table, $objects);

    /**
     * 获取 insert 插入后产生的 id
     *
     * @return int
     */
    public function getLastInsertId()
    {
        if ($this->connection === null) $this->connect();
        return $this->connection->lastInsertId();
    }

    /**
     * 获取当前数据库所有表信息
     *
     * @return array
     */
    public abstract function getTables();

    /**
     * 获取当前数据库所有表名
     *
     * @return array
     */
    public abstract function getTableNames();

    /**
     * 获取当前连接的所有库信息
     *
     * @return array
     */
    public abstract function getDatabases();

    /**
     * 获取当前连接的所有库名
     *
     * @return array
     */
    public abstract function getDatabaseNames();

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
    public abstract function getTableFields($table);

    /**
     * 获取指定表的主银
     *
     * @param string $table 表名
     * @return string | array | false
     */
    public abstract function getTablePrimaryKey($table);

    /**
     * 删除表
     *
     * @param string $table 表名
     */
    public abstract function dropTable($table);


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
        if ($this->connection === null) $this->connect();

        $this->transactions++;
        if ($this->transactions == 1) {
            $this->connection->beginTransaction();
        }
    }

    /**
     * 事务回滚
     *
     * @throws DbException
     */
    public function rollback()
    {
        if ($this->connection === null) $this->connect();
        $this->transactions--;
        if ($this->transactions == 0) {
            $this->connection->rollBack();
        }
    }

    /**
     * 事务提交
     * 
     * @throws DbException
     */
    public function commit()
    {
        if ($this->connection === null) $this->connect();

        $this->transactions--;
        if ($this->transactions == 0) {
            $this->connection->commit();
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
        if ($this->connection === null) $this->connect();
        return $this->connection->inTransaction();
    }

    /**
     * 获取数据库连接对象
     *
     * @return \PDO
     * @throws DbException
     */
    public function getConnection()
    {
        if ($this->connection === null) $this->connect();
        return $this->connection;
    }

    /**
     * 获取 版本号
     *
     * @return string
     * @throws DbException
     */
    public function getVersion()
    {
        if ($this->connection === null) $this->connect();
        return $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * 获取驱动名称 Mysql/Oracle/...
     *
     * @return string
     */
    public function getDriverName()
    {
        $class = get_called_class();
        $driverName = substr($class, strrpos($class, '\\') + 1);
        $driverName = str_replace('Impl', '', $driverName);

        return $driverName;
    }

    /**
     * 处理插入数据库的字段名或表名
     *
     * @param string $field
     * @return string
     */
    public abstract function quoteKey($field);

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
        if ($this->connection === null) $this->connect();
        return $this->connection->quote($value);
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
        if ($this->connection === null) $this->connect();

        $quotedValues = [];
        foreach ($values as $value) {
            $quotedValues[] = $this->connection->quote($value);
        }
        return $quotedValues;
    }

    /**
     * 处理插入数据库的字符串值，防注入, 仅处理敏感字符，不加外层引号，
     * 与 quoteValue 方法的区别可以理解为 quoteValue 比 escape 多了最外层的引号
     *
     * @param string $value
     * @return string
     */
    public abstract function escape($value);

}
