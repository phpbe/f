<?php

namespace Be\F\Runtime;

/**
 *  运行时
 */
class Driver
{

    protected $frameworkName = 'F'; // 框架名称 Mf/Sf/Ff

    protected $rootPath = null;

    protected $cacheDir = 'cache'; // 存放临时文件

    protected $dataDir = 'data'; // 存放系统生成的永久性文件，如配置文件

    protected $uploadDir = 'upload'; // 用户上传的数据

    /**
     * @var HttpServer
     */
    protected $httpServer = null;

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
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->rootPath . '/' . $this->cacheDir;
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
     * @param string $uploadDir
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * @return string
     */
    public function getUploadPath()
    {
        return $this->rootPath . '/' . $this->uploadDir;
    }

    public function execute()
    {

    }

    public function getHttpServer()
    {
        return $this->httpServer;
    }

}
