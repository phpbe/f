<?php

namespace Be\Framework\Plugin;

use Be\Framework\Be;

/**
 * 扩展基类
 */
abstract class Driver
{
    use \Be\Framework\Traits\Event;

    protected $setting = null;

    /**
     * 配置项
     *
     * @param array $setting
     * @return Driver
     */
    public function setting($setting = [])
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * 执行指定任务
     *
     * @param string $task
     */
    public function execute($task = null)
    {
        if ($task === null) {
            $task = Be::getRequest()->request('task', 'display');
        }

        if (method_exists($this, $task)) {
            $this->$task();
        }
    }

    /**
     * 默认输出方法
     */
    public function display() {

    }

}
