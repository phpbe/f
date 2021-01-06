<?php

namespace Be\Data\System\Config;

/**
 * @BeConfig("SESSION")
 */
class Session
{
    /**
     * @BeConfigItem("名称",
     *     driver="FormItemInput",
     *     description = "用在 cookie 或者 URL 中的会话名称， 例如：PHPSESSID。 只能使用字母和数字，建议尽可能的短一些")
     */
    public $name = 'SSID';

    /**
     * @BeConfigItem("SESSION 超时时间",
     *     driver="FormItemInputNumberInt",
     *     ui = "return [':min' => 1];")
     */
    public $expire = 1440;

    /**
     * @BeConfigItem("主机名",
     *     driver="FormItemInput",
     *     ui = "return [':min' => 1];")
     */
    public $host = '172.24.0.110';

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
