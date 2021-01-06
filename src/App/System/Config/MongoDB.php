<?php
namespace Be\App\System\Config;

/**
 * @BeConfig("MongoDB数据库配置")
 */
class MongoDB
{

    /**
     * @BeConfigItem("主库", driver="FormItemCode", language="json", valueType = "mixed")
     */
    public $master = [
        'host' => '172.24.0.120', // 主机名
        'port' => 27017, // 端口号
        'db' => '' // 数据库
    ];

}
