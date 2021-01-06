<?php

namespace Be\Framework\Runtime;


/**
 *  运行时
 * @package System
 *
 */
class Driver
{
    protected $rootPath = null;

    protected $dataDir = 'data';

    protected $cacheDir = 'cache';

    public function __construct()
    {
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
    public function rootPath()
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
    public function dataDir()
    {
        return $this->dataDir;
    }

    /**
     * @return string
     */
    public function dataPath()
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
    public function cachePath()
    {
        return $this->rootPath . '/' . $this->cacheDir;
    }

    /**
     * @return string
     */
    public function cacheDir()
    {
        return $this->cacheDir;
    }


    public function execute() {

    }

}
