<?php
namespace Be\Cache\System\Tuple\master;

class system_role extends \Be\System\Db\Tuple
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_role'; // 表名
    protected $_primaryKey = 'id'; // 主键
    public $id = 0; // 自增编号
    public $name = ''; // 角色名
    public $remark = ''; // 备注
    public $permission = 0; // 权限
    public $permissions = ''; // 权限明细
    public $is_enable = 1; // 是否可用
    public $is_delete = 0; // 是否已删除
    public $ordering = 0; // 排序（越小越靠前）
    public $create_time = 'CURRENT_TIMESTAMP'; // 创建时间
    public $update_time = 'CURRENT_TIMESTAMP'; // 更新时间
}

