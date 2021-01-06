<?php

namespace Be\App\System\Config;

/**
 * @BeConfig("缓存")
 */
class Cache
{
    /**
     * @BeConfigItem("主机名",
     *     driver="FormItemInput",
     *     ui = "return [':min' => 1];")
     */
    public $host = '127.0.0.1';

    /**
     * @BeConfigItem("端口号",
     *     driver="FormItemInputNumberInt",
     *     ui = "return [':min' => 1, ':max' => 65535];")
     */
    public $port = 6379;

    /**
     * @BeConfigItem("连接超时时间",
     *     driver="FormItemInputNumberInt",
     *     ui = "return [':min' => 1];")
     */
    public $timeout = 5;

    /**
     * @BeConfigItem("验证密码",
     *     driver="FormItemInput")
     */
    public $auth = '';

    /**
     * @BeConfigItem("数据库",
     *     driver="FormItemInputNumberInt",
     *     ui = "return [':min' => 0, ':max' => 15];")
     */
    public $db = 0;

}
