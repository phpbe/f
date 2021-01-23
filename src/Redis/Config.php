<?php
namespace Be\F\Redis;


class Config
{
    /**
     * @BeConfigItem("主库", driver="FormItemCode", language="json", valueType = "mixed")
     */
    public $master = [
        'host' => '127.0.0.1', // 主机名
        'port' => 6379, // 端口号
        'timeout' => 5, // 超时时间
        'auth' => '', // 密码，不需要时留空
        'db' => 0, // 默认选中的数据库接
        'pool' => 0, // 连接池，<=0 时不启用
    ];

}
