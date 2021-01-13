<?php

namespace Be\F\Runtime;


/**
 *  运行时
 * @package System
 *
 */
class Driver
{

    protected $frameworkName = 'F'; // 框架名称 Mf/Sf/Ff

    protected $rootPath = null;

    protected $dataDir = 'data';

    protected $cacheDir = 'cache';

    public function __construct()
    {
    }

    /**
     * 获取框架名称
     *
     * @return string
     */
    public function getFrameworkName()
    {
        return $this->frameworkName;
    }

    /**
     * 设置BE框架的根路径
     *
     * @param string $rootPath BE框架的根路径，绝对路径
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * 获取BE框架的根路径
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @param string $dataDir
     */
    public function setDataDir($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    /**
     * @return string
     */
    public function getDataDir()
    {
        return $this->dataDir;
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->rootPath . '/' . $this->dataDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->rootPath . '/' . $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    public function execute() {

    }

}
