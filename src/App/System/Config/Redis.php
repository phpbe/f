<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("Redis")
 */
class Redis
{
    /**
     * @BeConfigItem("主库", driver="FormItemCode", language="json", valueType = "mixed")
     */
    public $master = [
        'host' => '172.24.0.110', // 主机名
        'port' => 6379, // 端口号
        'timeout' => 60, // 超时时间
        'persistent' => false, // 是否使用长连接
        'password' => '', // 密码，不需要时留空
        'db' => 0 // 默认选中的数据库接
    ];


}
