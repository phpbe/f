<?php

namespace Be\Framework;

use Be\Framework\Runtime\RuntimeException;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    public static $cache = []; // 缓存资源实例

    /**
     * @var \Be\Framework\Runtime\Driver
     */
    protected static $runtime = null; // 系统运行时

    /**
     * 获取请求对象
     *
     * @return \Be\Framework\Request\Driver
     */
    public static function getRequest() {
        return self::$cache[\Swoole\Coroutine::getuid()]['Request'];
    }

    /**
     * 获取输出对象
     *
     * @return \Be\Framework\Response\Driver
     */
    public static function getResponse() {
        return self::$cache[\Swoole\Coroutine::getuid()]['Response'];
    }

    /**
     * 获取SESSION
     *
     * @return \Be\Framework\Session\Driver
     */
    public static function getSession()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Session'])) return self::$cache[$cid]['Session'];
        self::$cache[$cid]['Session'] = new \Be\Framework\Session\Driver();
        return self::$cache[$cid]['Session'];
    }

    /**
     * 获取Cache
     *
     * @return \Be\Framework\Cache\Driver
     */
    public static function getCache()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Cache'])) return self::$cache[$cid]['Cache'];
        self::$cache[$cid]['Cache'] = new \Be\Framework\Cache\Driver();
        return self::$cache[$cid]['Cache'];
    }

    /**
     * 获取日志记录器
     *
     * @return \Be\Framework\Logger\Driver
     */
    public static function getLogger()
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Logger'])) return self::$cache[$cid]['Logger'];
        self::$cache[$cid]['Logger'] = new \Be\Framework\Logger\Driver();
        return self::$cache[$cid]['Logger'];
    }

    /**
     * 获取数据库对象（单例）
     *
     * @param string $name 数据库名
     * @return \Be\Framework\Db\Driver
     * @throws RuntimeException
     */
    public static function getDb($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Db'][$name])) return self::$cache[$cid]['Db'][$name];
        self::$cache[$cid]['Db'][$name] = self::newDb($name);
        return self::$cache[$cid]['Db'][$name];
    }

    /**
     * 获取有效期的数据库对象（单例）
     * 如果实例已创建时间超过了有效期，则创建新实例
     *
     * @param string $name 数据库名
     * @param int $expire 有效时间(单位：秒)
     * @return \Be\Framework\Db\Driver
     * @throws RuntimeException
     */
    public static function getExpireDb($name = 'master', $expire = 600)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['ExpireDb'][$name]['expire']) && self::$cache[$cid]['ExpireDb'][$name]['expire'] > time()) {
            return self::$cache[$cid]['ExpireDb'][$name]['instance'];
        }

        self::$cache[$cid]['ExpireDb'][$name] = [
            'expire' => time() + $expire,
            'instance' => self::newDb($name)
        ];
        return self::$cache[$cid]['ExpireDb'][$name]['instance'];
    }

    /**
     * 新创建一个数据库对象
     *
     * @param string $name 数据库名
     * @return \Be\Framework\Db\Driver
     * @throws RuntimeException
     */
    public static function newDb($name = 'master')
    {
        $config = self::getConfig('System.Db');
        if (!isset($config->$name)) {
            throw new \RuntimeException('数据库配置项（' . $name . '）不存在！');
        }

        $config = $config->$name;

        $class = 'Be\\System\\Db\\Driver\\' . ucfirst($config['driver']);
        if (!class_exists($class)) throw new \RuntimeException('数据库配置项（' . $name . '）指定的数据库驱动' . ucfirst($config['driver']) . '不支持！');

        return new $class($config);
    }

    /**
     * 获取Redis对象（单例）
     *
     * @param string $name Redis名
     * @return \Be\Framework\Redis\Driver
     * @throws RuntimeException
     */
    public static function getRedis($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Redis'][$name])) return self::$cache[$cid]['Redis'][$name];
        self::$cache[$cid]['Redis'][$name] = self::newRedis($name);
        return self::$cache[$cid]['Redis'][$name];
    }

    /**
     * 新创建一个Redis对象
     *
     * @param string $redis Redis名
     * @return \Be\Framework\Redis\Driver
     * @throws RuntimeException
     */
    public static function newRedis($redis = 'master')
    {
        $config = self::getConfig('System.Redis');
        if (!isset($config->$redis)) {
            throw new RuntimeException('Redis配置项（' . $redis . '）不存在！');
        }

        return new \Be\Framework\Redis\Driver($config->$redis);
    }

    /**
     * 获取MongoDB对象（单例）
     *
     * @param string $name MongoDB名
     * @return \Be\Framework\MongoDB\Driver
     * @throws RuntimeException
     */
    public static function getMongoDB($name = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['MongoDB'][$name])) return self::$cache[$cid]['MongoDB'][$name];
        self::$cache[$cid]['MongoDB'][$name] = self::newMongoDB($name);
        return self::$cache[$cid]['MongoDB'][$name];
    }

    /**
     * 新创建一个MongoDB对象
     *
     * @param string $mongoDB MongoDB名
     * @return \Be\Framework\MongoDB\Driver
     * @throws RuntimeException
     */
    public static function newMongoDB($name = 'master')
    {
        $config = self::getConfig('System.MongoDB');
        if (!isset($config->$name)) {
            throw new RuntimeException('MongoDB配置项（' . $name . '）不存在！');
        }
        return new \Be\Framework\MongoDB\Driver($config->$name);
    }

    /**
     * 获取指定的库（单例）
     *
     * @param string $name 库名，可指定命名空间，调用第三方库
     * @return mixed
     * @throws RuntimeException
     */
    public static function getLib($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Lib'][$name])) return self::$cache[$cid]['Lib'][$name];
        self::$cache[$cid]['Lib'][$name] = self::newLib($name);
        return self::$cache[$cid]['Lib'][$name];
    }

    /**
     * 新创建一个指定的库
     *
     * @param string $name 库名，可指定命名空间，调用第三方库
     * @return mixed
     * @throws RuntimeException
     */
    public static function newLib($name)
    {
        $class = null;
        if (strpos($name, '\\') === false) {
            $class = 'Be\\Lib\\' . $name . '\\' . $name;
        } else {
            $class = $name;
        }
        if (!class_exists($class)) throw new RuntimeException('库 ' . $class . ' 不存在！');

        return new $class();
    }

    /**
     * 获取指定的一个扩展（单例）
     *
     * @param string $name 扩展名
     * @return mixed
     * @throws RuntimeException
     */
    public static function getPlugin($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Plugin'][$name])) return self::$cache[$cid]['Plugin'][$name];
        self::$cache[$cid]['Plugin'][$name] = self::newPlugin($name);
        return self::$cache[$cid]['Plugin'][$name];
    }

    /**
     * 新创建一个指定的扩展
     *
     * @param string $name 扩展名
     * @return mixed
     * @throws RuntimeException
     */
    public static function newPlugin($name)
    {
        $class = 'Be\\Plugin\\' . $name . '\\' . $name;
        if (!class_exists($class)) {
            throw new RuntimeException('扩展 ' . $name . ' 不存在！');
        }

        return new $class();
    }

    /**
     * 获取指定的配置文件（单例）
     *
     * @param string $name 配置文件名
     * @return mixed
     * @throws RuntimeException
     */
    public static function getConfig($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Config'][$name])) return self::$cache[$cid]['Config'][$name];
        self::$cache[$cid]['Config'][$name] = self::newConfig($name);
        return self::$cache[$cid]['Config'][$name];
    }

    /**
     * 新创建一个指定的配置文件
     *
     * @param string $name 配置文件名
     * @return mixed
     * @throws RuntimeException
     */
    public static function newConfig($name)
    {
        $class = 'Be\\Data\\Config\\' . $name;
        if (class_exists($class)) {
            return new $class();
        }

        $class = 'Be\\Config\\' . $name;
        if (class_exists($class)) {
            return new $class();
        }

        throw new RuntimeException('配置文件 ' . $name . ' 不存在！');
    }

    /**
     * 获取指定的一个服务（单例）
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function getService($name)
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Service'][$name])) return self::$cache[$cid]['Service'][$name];
        self::$cache[$cid]['Service'][$name] = self::newService($name);
        return self::$cache[$cid]['Service'][$name];
    }

    /**
     * 新创建一个服务
     *
     * @param string $name 服务名
     * @return mixed
     */
    public static function newService($name)
    {
        $parts = explode('.', $name);
        $app = array_shift($parts);
        $class = 'Be\\App\\' . $app . '\\Service\\' . implode('\\', $parts);
        return new $class();
    }

    /**
     * 获取指定的一个数据库行记灵对象（单例）
     *
     * @param string $name 数据库行记灵对象名
     * @param string $db 库名
     * @return \Be\Framework\Db\Tuple | mixed
     */
    public static function getTuple($name, $db = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Tuple'][$db][$name])) return self::$cache[$cid]['Tuple'][$db][$name];
        self::$cache[$cid]['Tuple'][$db][$name] = self::newTuple($name);
        return self::$cache[$cid]['Tuple'][$db][$name];
    }

    /**
     * 新创建一个数据库行记灵对象
     *
     * @param string $name 数据库行记灵对象名
     * @param string $db 库名
     * @return \Be\Framework\Db\Tuple | mixed
     */
    public static function newTuple($name, $db = 'master')
    {
        $path = self::$runtime->cachePath() . '/System/Tuple/'.$db.'/'.$name.'.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Db');
            $service->updateTuple($name, $db);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Tuple\\' . $db . '\\' . $name;
        return (new $class());
    }

    /**
     * 获取指定的一个数据库表对象（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\Framework\Db\Table
     */
    public static function getTable($name, $db = 'master')
    {
        $cid = \Swoole\Coroutine::getuid();
        if (isset(self::$cache[$cid]['Table'][$db][$name])) return self::$cache[$cid]['Table'][$db][$name];
        self::$cache[$cid]['Table'][$db][$name] = self::newTable($name, $db);
        return self::$cache[$cid]['Table'][$db][$name];
    }

    /**
     * 新创建一个数据库表对象
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\Framework\Db\Table
     */
    public static function newTable($name, $db = 'master')
    {
        $path = self::$runtime->cachePath() . '/System/Table/'.$db.'/'.$name.'.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Db');
            $service->updateTable($name, $db);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Table\\' . $db . '\\' . $name;
        return (new $class());
    }

    /**
     * 获取指定的一个数据库表属性（单例）
     *
     * @param string $name 表名
     * @param string $db 库名
     * @return \Be\Framework\Db\TableProperty
     */
    public static function getTableProperty($name, $db = 'master')
    {
        if (isset(self::$cache['TableProperty'][$db][$name])) return self::$cache['TableProperty'][$db][$name];

        $path = self::$runtime->cachePath() . '/System/TableProperty/'.$db.'/'.$name.'.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Db');
            $service->updateTableProperty($name, $db);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\TableProperty\\' . $db . '\\' . $name;
        self::$cache['TableProperty'][$db][$name] = new $class();
        return self::$cache['TableProperty'][$db][$name];
    }

    /**
     * 获取指定的一个菜单（单例）
     *
     * @return \Be\Framework\Menu\Driver
     */
    public static function getMenu()
    {
        if (isset(self::$cache['Menu'])) return self::$cache['Menu'];

        $path = self::$runtime->cachePath() . '/System/Menu.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Menu');
            $service->update();
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Menu';
        self::$cache['Menu'] = new $class();
        return self::$cache['Menu'];
    }

    /**
     * 获取指定的一个角色信息（单例）
     *
     * @param int $roleId 角色ID
     * @return Role
     */
    public static function getRole($roleId)
    {
        if (isset(self::$cache['Role'][$roleId])) return self::$cache['Role'][$roleId];

        $path = self::$runtime->cachePath() . '/System/Role/Role' . $roleId . '.php';
        $configSystem = self::getConfig('System.System');
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Role');
            $service->updateRole($roleId);
            include_once $path;
        }

        $class = 'Be\\Cache\\System\\Role\\Role' . $roleId;
        self::$cache['Role'][$roleId] = new $class();
        return self::$cache['Role'][$roleId];
    }

    /**
     * 获取一个属性（单例）
     *
     * @param string $name 名称
     * @return Property
     * @throws RuntimeException
     */
    public static function getProperty($name)
    {
        if (isset(self::$cache['Property'][$name])) return self::$cache['Property'][$name];

        $parts = explode('.', $name);
        $class = 'Be\\' . implode('\\', $parts) . '\\Property';
        if (!class_exists($class)) throw new RuntimeException('属性 ' . $name . ' 不存在！');
        $instance = new $class();

        self::$cache['Property'][$name] = $instance;
        return self::$cache['Property'][$name];
    }

    /**
     * 获取指定的一个模板（单例）
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     * @return Template
     * @throws RuntimeException
     */
    public static function getTemplate($template, $theme = null)
    {
        $cid = self::getCoroutineId();
        $parts = explode('.', $template);
        $type = array_shift($parts);
        $name = array_shift($parts);

        $configSystem = self::getConfig('System.System');

        if ($theme === null) {
            $property = Be::getProperty($type . '.' . $name);
            if (isset($property->theme)) {
                $theme = $property->theme;
            } else {
                $theme = $configSystem->theme;
            }
        }

        if (isset(self::$cache[$cid]['Template'][$theme][$template])) return self::$cache[$cid]['Template'][$theme][$template];

        $path = self::$runtime->cachePath() . '/System/Template/' . $theme . '/' . $type . '/' . $name . '/' . implode('/', $parts) . '.php';
        if ($configSystem->developer || !file_exists($path)) {
            $service = self::getService('System.Template');
            $service->update($template, $theme);
        }

        $class = 'Be\\Cache\\System\\Template\\' . $theme . '\\' . $type . '\\' . $name . '\\' . implode('\\', $parts);
        self::$cache[$cid]['Template'][$theme][$template] = new $class();
        return self::$cache[$cid]['Template'][$theme][$template];
    }

    /**
     * 获取一个用户 实例（单例）
     *
     * @param int $id 用户编号
     * @return User | mixed
     */
    public static function getUser($id = 0)
    {
        if (isset(self::$cache['User'][$id])) return self::$cache['User'][$id];

        $user = null;
        if ($id == 0) {
            $user = self::getSession()->get('_user');
        } else {
            $user = self::getTuple('system_user')->load($id)->toObject();
            if ($user) {
                unset($user->password, $user->salt, $user->remember_me_token);
            }
        }

        self::$cache['User'][$id] = new User($user);
        return self::$cache['User'][$id];
    }

    public static function getRuntime()
    {
        if (self::$runtime == null) {
            self::$runtime = new \Be\Framework\Runtime\Driver();
        }
        return self::$runtime;
    }

}
