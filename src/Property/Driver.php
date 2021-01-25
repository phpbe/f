<?php
namespace Be\F\Property;

use Be\F\Request\RequestFactory;
use Be\F\Runtime\RuntimeException;
use Be\F\Runtime\RuntimeFactory;

/**
 * 属性基类
 */
abstract class Driver
{
    protected $name = null; // 名称
    protected $label = ''; // 中文名
    protected $icon = ''; // 图标
    protected $description = ''; // 描述

    protected $path = null; // 路径，相对于根路径

    /**
     * 构造函数
     * @param string $path 文件咱径
     */
    public function __construct($path = '')
    {
        $class = get_called_class();
        $name = substr($class, 0, strrpos($class, '\\'));
        $name = substr($name, strrpos($name, '\\')+1);
        $this->name = $name;

        $this->path = str_replace(RuntimeFactory::getInstance()->getRootPath(), '', substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR)));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return null | string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 获取网址
     *
     * @return string
     */
    public function getUrl() {
        return RequestFactory::getInstance()->getRootUrl() . $this->path;
    }

    public function __get($name) {
        if( isset( $this->$name ) ) {
            return $this->$name;
        } else {
            throw new RuntimeException($name . ' 属性未定义！');
        }
    }

}
