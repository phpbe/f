<?php
namespace Be\F\Es;


class Config
{

    /**
     * @BeConfigItem("ES服务器", driver="FormItemCode", language="json", valueType = "array(string)")
     */
    public $hosts = ['127.0.0.1:9200'];

}
