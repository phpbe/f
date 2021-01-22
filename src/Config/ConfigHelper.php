<?php

namespace Be\F\Config;

use Be\F\Runtime\RuntimeFactory;

class ConfigHelper
{

    /**
     * 保存配置
     *
     * @param string $name 配置名称，格式：应用名.配置名
     * @param object $instance 配置实例
     */
    public static function update($name, $instance)
    {
        $parts = explode('.', $name);
        $appName = $parts[0];
        $configName = $parts[1];

        $runtime = RuntimeFactory::getInstance();
        $frameworkName = $runtime->getFrameworkName();

        $code = "<?php\n";
        $code .= 'namespace Be\\' . $frameworkName . '\\Data\\' . $appName . '\\Config;' . "\n\n";
        $code .= 'class ' . $configName . "\n";
        $code .= "{\n";

        $vars = get_object_vars($instance);
        foreach ($vars as $k => $v) {
            $code .= '  public $' . $k . ' = ' . var_export($v, true) . ';' . "\n";
        }
        $code .= "}\n";

        $path = $runtime->getDataPath() . '/' . $appName . '/Config/' . $configName . '.php';
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($path, $code, LOCK_EX);
        chmod($path, 0755);
    }

}


