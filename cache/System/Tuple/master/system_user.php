<?php
namespace Be\Cache\System\Tuple\master;

class system_user extends \Be\System\Db\Tuple
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_user'; // 表名
    protected $_primaryKey = 'id'; // 主键
    public $id = 0; // 自增编号
    public $username = ''; // 用户名
    public $password = ''; // 密码
    public $salt = ''; // 密码盐值
    public $role_id = 0; // 角色ID
    public $avatar = ''; // 头像
    public $email = ''; // 邮箱
    public $name = ''; // 名称
    public $gender = -1; // 性别（0：女/1：男/-1：保密）
    public $phone = ''; // 电话
    public $mobile = ''; // 手机
    public $last_login_time = '0000-00-00 00:00:00'; // 最后一次登陆时间
    public $this_login_time = '0000-00-00 00:00:00'; // 本次登陆时间
    public $last_login_ip = ''; // 最后一次登录的IP
    public $this_login_ip = ''; // 本次登录的IP
    public $is_enable = 1; // 是否可用
    public $is_delete = 0; // 是否已删除
    public $create_time = 'CURRENT_TIMESTAMP'; // 创建时间
    public $update_time = 'CURRENT_TIMESTAMP'; // 更新时间
}

