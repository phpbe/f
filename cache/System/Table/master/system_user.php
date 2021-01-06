<?php
namespace Be\Cache\System\Table\master;

class system_user extends \Be\System\Db\Table
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_user'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = ['id','username','password','salt','role_id','avatar','email','name','gender','phone','mobile','last_login_time','this_login_time','last_login_ip','this_login_ip','is_enable','is_delete','create_time','update_time']; // 字段列表
}

