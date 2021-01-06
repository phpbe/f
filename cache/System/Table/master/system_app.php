<?php
namespace Be\Cache\System\Table\master;

class system_app extends \Be\System\Db\Table
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_app'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = ['id','name','label','icon','ordering','install_time','update_time']; // 字段列表
}

