<?php
namespace Be\F\Logger;


class Config
{
    /**
     * @BeConfigItem("日志级别",
     *     driver="FormItemSelect",
     *     values = "return ['debug','info','notice','warning','error','critical','alert','emergency'];")
     */
    public $level = 'debug';

    /**
     * @BeConfigItem("记录 $_GET", driver="FormItemSwitch")
     */
    public $get = true;

    /**
     * @BeConfigItem("记录 $_POST", driver="FormItemSwitch")
     */
    public $post = true;

    /**
     * @BeConfigItem("记录 $_REQUEST", driver="FormItemSwitch")
     */
    public $request = true;

    /**
     * @BeConfigItem("记录 $_COOKIE", driver="FormItemSwitch")
     */
    public $cookie = true;

    /**
     * @BeConfigItem("记录 $_SESSION", driver="FormItemSwitch")
     */
    public $session = true;

    /**
     * @BeConfigItem("记录 $_SERVER", driver="FormItemSwitch")
     */
    public $server = true;

    /**
     * @BeConfigItem("记录内存占用", driver="FormItemSwitch")
     */
    public $memery = true;

}

