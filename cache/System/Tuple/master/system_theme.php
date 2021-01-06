<?php
namespace Be\Cache\System\Tuple\master;

class system_theme extends \Be\System\Db\Tuple
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_theme'; // 表名
    protected $_primaryKey = 'id'; // 主键
    public $id = 0; // 自增ID
    public $name = ''; // 应用名
    public $label = ''; // 应用中文标识
    public $install_time = 'CURRENT_TIMESTAMP'; // 安装时间
    public $update_time = 'CURRENT_TIMESTAMP'; // 更新时间
}

