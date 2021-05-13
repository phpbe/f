<?php
namespace Be\F\Db;

class Config
{

    /**
     * @BeConfigItem("主库", driver="FormItemCode", language="json", valueType = "mixed")
     */
    public $master = [
        'driver' => 'mysql',
        'host' => '127.0.0.1', // 主机名
        'port' => 3306, // 端口号
        'username' => 'root', // 用户名
        'password' => 'root', // 密码
        'name' => 'be', // 数据库名称
        'charset' => 'UTF8', // 字符集
        'pool' => 0, // 连接池，<=0 时不启用
    ]; // 主数据库

}
