<?php
namespace Be\Cache\System\Table\master;

class system_user_login_log extends \Be\System\Db\Table
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_user_login_log'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = ['id','username','success','description','ip','create_time']; // 字段列表
}

