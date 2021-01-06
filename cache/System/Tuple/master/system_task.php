<?php
namespace Be\Cache\System\Tuple\master;

class system_task extends \Be\System\Db\Tuple
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_task'; // 表名
    protected $_primaryKey = 'id'; // 主键
    public $id = 0; // 自增ID
    public $name = ''; // 名称
    public $driver = ''; // 驱动
    public $schedule = '* * * * *'; // 执行计划
    public $is_enable = 1; // 是否可用
    public $last_execute_time = '0000-00-00 00:00:00'; // 最后执行时间
    public $create_time = 'CURRENT_TIMESTAMP'; // 创建时间
    public $update_time = 'CURRENT_TIMESTAMP'; // 更新时间
}

