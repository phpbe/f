<?php
namespace Be\Cache\System\Table\master;

class system_task extends \Be\System\Db\Table
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_task'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = ['id','name','driver','params','schedule','is_enable','last_execute_time','create_time','update_time']; // 字段列表
}

