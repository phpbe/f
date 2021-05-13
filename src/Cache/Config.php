<?php

namespace Be\F\Cache;

class Config
{

    /**
     * @BeConfigItem("驱动",
     *     driver="FormItemSelect",
     *     keyValues = "return ['File' => '文件', 'Redis' => 'Redis'];")
     */
    public $driver = 'File';

    /**
     * @BeConfigItem("REDIS库",
     *     driver="FormItemSelect",
     *     keyValues = "return \Be\F\Cache\CacheHelper::getConfigRedisKeyValues();",
     *     ui="return ['form-item' => ['v-show' => 'formData.driver == \'Redis\'']];")
     */
    public $redis = 'master';

}
