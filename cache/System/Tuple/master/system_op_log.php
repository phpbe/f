<?php
namespace Be\Cache\System\Tuple\master;

class system_op_log extends \Be\System\Db\Tuple
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_op_log'; // 表名
    protected $_primaryKey = 'id'; // 主键
    public $id = 0; // 自增编号
    public $user_id = 0; // 用户ID
    public $app = ''; // 应用名
    public $controller = ''; // 控制器名
    public $action = ''; // 动作名
    public $content = ''; // 内容
    public $details = ''; // 明细
    public $ip = ''; // IP
    public $create_time = 'CURRENT_TIMESTAMP'; // 创建时间
}

