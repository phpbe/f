<?php
namespace Be\Cache\System\Tuple\master;

class system_user_login_log extends \Be\System\Db\Tuple
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_user_login_log'; // 表名
    protected $_primaryKey = 'id'; // 主键
    public $id = 0;
    public $username = '';
    public $success = 0;
    public $description = '';
    public $ip = '';
    public $create_time = '0000-00-00 00:00:00'; // 创建时间
}

