<?php
namespace Be\F\Log;


class Config
{
    /**
     * @BeConfigItem("日志级别",
     *     driver="FormItemSelect",
     *     values = "return ['debug','info','notice','warning','error','critical','alert','emergency'];")
     */
    public $level = 'debug';

    /**
     * @BeConfigItem("记录 GET", driver="FormItemSwitch")
     */
    public $get = true;

    /**
     * @BeConfigItem("记录 POST", driver="FormItemSwitch")
     */
    public $post = true;

    /**
     * @BeConfigItem("记录 REQUEST", driver="FormItemSwitch")
     */
    public $request = true;

    /**
     * @BeConfigItem("记录 COOKIE", driver="FormItemSwitch")
     */
    public $cookie = true;

    /**
     * @BeConfigItem("记录 SESSION", driver="FormItemSwitch")
     */
    public $session = true;

    /**
     * @BeConfigItem("记录 SERVER", driver="FormItemSwitch")
     */
    public $server = true;

    /**
     * @BeConfigItem("记录内存占用", driver="FormItemSwitch")
     */
    public $memery = true;

}

