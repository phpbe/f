<?php
namespace Be\Cache\System\TableProperty\master;

class system_user_login_log extends \Be\System\Db\TableProperty
{
    protected $_dbName = 'master'; // 数据库名
    protected $_tableName = 'system_user_login_log'; // 表名
    protected $_primaryKey = 'id'; // 主键
    protected $_fields = array (
  'id' => 
  array (
    'name' => 'id',
    'type' => 'int',
    'length' => '11',
    'precision' => '',
    'scale' => '',
    'comment' => '',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => 'PRI',
    'extra' => 'auto_increment',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 1,
    'isNumber' => 1,
  ),
  'username' => 
  array (
    'name' => 'username',
    'type' => 'varchar',
    'length' => '120',
    'precision' => '',
    'scale' => '',
    'comment' => '',
    'default' => '',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'success' => 
  array (
    'name' => 'success',
    'type' => 'tinyint',
    'length' => '1',
    'precision' => '',
    'scale' => '',
    'comment' => '',
    'default' => NULL,
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 1,
  ),
  'description' => 
  array (
    'name' => 'description',
    'type' => 'varchar',
    'length' => '240',
    'precision' => '',
    'scale' => '',
    'comment' => '',
    'default' => '',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'ip' => 
  array (
    'name' => 'ip',
    'type' => 'varchar',
    'length' => '15',
    'precision' => '',
    'scale' => '',
    'comment' => '',
    'default' => '',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => 'utf8_general_ci',
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
  'create_time' => 
  array (
    'name' => 'create_time',
    'type' => 'timestamp',
    'length' => '',
    'precision' => '',
    'scale' => '',
    'comment' => '创建时间',
    'default' => '0000-00-00 00:00:00',
    'nullAble' => false,
    'unsigned' => false,
    'collation' => NULL,
    'key' => '',
    'extra' => '',
    'privileges' => 'select,insert,update,references',
    'autoIncrement' => 0,
    'isNumber' => 0,
  ),
); // 字段列表
}

