<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("数据库")
 */
class Db
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
    ]; // 主数据库

}
