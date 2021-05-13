<?php

namespace Be\F\Cache\Driver;

use Be\F\Cache\Driver;
use Be\F\Runtime\RuntimeFactory;

/**
 * 缓存驱动
 */
class File implements Driver
{

    private $path = null;

    /**
     * 构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct($config = [])
    {
        $this->path = RuntimeFactory::getInstance()->getCachePath() . '/cache';
    }

    /**
     * 关闭
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * 获取 指定的缓存 值
     *
     * @param string $key 键名
     * @return mixed|false
     */
    public function get($key)
    {
        $hash = sha1($key);
        $path = $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.php';

        if (!is_file($path)) return false;

        $content = file_get_contents($path);

        if (false !== $content) {
            $expire = substr($content, 8, 10);
            if (time() > intval($expire)) {
                unlink($path);
                return false;
            }

            $value = substr($content, 18);
            if (!is_bool($value) && !is_numeric($value)) $value = unserialize($value);
            return $value;
        } else {
            return false;
        }
    }

    /**
     * 获取 多个指定的缓存 值
     *
     * @param array $keys 键名 数组
     * @return array()
     */
    public function getMany($keys)
    {
        $values = array();
        foreach ($keys as $key) {
            $values[] = $this->get($key);
        }
        return $values;
    }

    /**
     * 设置缓存
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int $expire 有效时间（秒）
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        $hash = sha1($key);
        $dir = $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2);
        if (!is_dir($dir)) mkdir($dir, 0777, 1);
        $path = $dir . '/' . $hash . '.php';

        if (!is_bool($value) && !is_numeric($value)) $value = serialize($value);

        if ($expire == 0) {
            $expire = 9999999999;
        } else {
            $expire = time() + $expire;
            if ($expire > 9999999999) $expire = 9999999999;
        }
        $data = "<?php\n//" . $expire . $value;
        return file_put_contents($path, $data);
    }

    /**
     * 设置缓存
     *
     * @param array $values 键值对
     * @param int $expire 有效时间（秒）
     * @return bool
     */
    public function setMany($values, $expire = 0)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $expire);
        }
        return true;
    }

    /**
     * 指定键名的缓存是否存在
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function has($key)
    {
        $hash = sha1($key);
        $path = $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.php';

        return is_file($path) ? true : false;
    }

    /**
     * 删除指定键名的缓存
     *
     * @param string $key 缓存键名
     * @return bool
     */
    public function delete($key)
    {
        $hash = sha1($key);
        $path = $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.php';
        if (!is_file($path)) return true;
        return unlink($path);
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function increment($key, $step = 1)
    {
        $hash = sha1($key);
        $dir = $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2);
        if (!is_dir($dir)) mkdir($dir, 0777, 1);
        $path = $dir . '/' . $hash . '.php';

        if (!is_file($path)) {
            $value = $step;
            $data = "<?php\n//9999999999" . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        }

        $content = file_get_contents($path);

        if (false !== $content) {
            $expire = substr($content, 8, 10);
            if (time() > intval($expire)) return false;

            $content = substr($content, 18);
            $value = intval($content) + $step;
            $data = "<?php\n//" . $expire . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        } else {
            return false;
        }
    }

    /**
     * 自减缓存（针对数值缓存）
     *
     * @param string $key 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function decrement($key, $step = 1)
    {
        $hash = sha1($key);
        $dir = $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2);
        if (!is_dir($dir)) mkdir($dir, 0777, 1);
        $path = $dir . '/' . $hash . '.php';

        if (!is_file($path)) {
            $value = -$step;
            $data = "<?php\n//9999999999" . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        }

        $content = file_get_contents($path);

        if (false !== $content) {
            $expire = substr($content, 8, 10);
            if (time() > intval($expire)) return false;

            $content = substr($content, 18);
            $value = intval($content) - $step;
            $data = "<?php\n//" . $expire . $value;
            if (!file_put_contents($path, $data)) return false;
            return $value;
        } else {
            return false;
        }
    }

    /**
     * 清除缓存
     *
     * @return bool
     */
    public function flush()
    {
        $handle = opendir($this->path);
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                \Be\F\Util\FileSystem\Dir::rm($this->path . '/' . $file);
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * 缓存代理
     *
     * @param string $name 键名
     * @param callable $callable 匿名函数，无参数
     * @param int $expire 超时时间
     * @return mixed
     */
    public function proxy($name, $callable, $expire = 0)
    {
        $name = 'proxy:' . $name;

        if ($this->has($name)) {
            return $this->get($name);
        }

        $value = $callable();
        $this->set($name, $value, $expire);

        return $value;
    }

}
