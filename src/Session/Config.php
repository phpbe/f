<?php

namespace Be\F\Session;

class Config
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
     * @BeConfigItem("驱动",
     *     driver="FormItemSelect",
     *     keyValues = "return ['File' => '文件', 'Redis' => 'Redis'];")
     */
    public $driver = 'File';

    /**
     * @BeConfigItem("REDIS库",
     *     driver="FormItemSelect",
     *     keyValues = "return \Be\F\Session\SessionHelper::getConfigRedisKeyValues();",
     *     ui="return ['form-item' => ['v-show' => 'formData.driver == \'Redis\'']];")
     */
    public $redis = 'master';

}
