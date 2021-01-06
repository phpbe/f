<?php
namespace Be\Framework\db;

/**
 * Class TableProperty
 * @package \Be\Framework\Db
 */
class TableProperty
{
    /**
     * 数据库名
     *
     * @var string
     */
    protected $_dbName = 'master';
    /**
     * 表名
     *
     * @var string
     */
    protected $_tableName = '';

    /**
     * 主键
     *
     * @var null | string | array
     */
    protected $_primaryKey = null;

    /**
     * 字段明细列表
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * 获取数据库名
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->_dbName;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * 获取主键名
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }

    /**
     * 获取字段明细列表
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * 获取指定字段
     *
     * @param string $fieldName 字段名
     * @return array
     */
    public function getField($fieldName)
    {
        return isset($this->_fields[$fieldName]) ? $this->_fields[$fieldName] : null;
    }


}
