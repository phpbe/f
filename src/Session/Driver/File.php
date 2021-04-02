<?php

namespace Be\F\Session\Driver;

use Be\F\Runtime\RuntimeFactory;
use Be\F\Session\Driver;

/**
 * Session
 */
class File extends Driver
{

    public function read()
    {
        if ($this->data === null) {
            $path = RuntimeFactory::getInstance()->getCachePath() . '/session/' . $this->id;

            $data = [];
            if (file_exists($path)) {
                $content = file_get_contents($path);
                if (false !== $content) {
                    $expire = substr($content, 0, 10);
                    if (time() > intval($expire)) {
                        unlink($path);
                    } else {
                        $dataContent = substr($content, 10);
                        $data = unserialize($dataContent);
                    }
                }
            }

            $this->data = $data;
        }
    }

    public function write()
    {
        if ($this->data !== null) {
            $dir = RuntimeFactory::getInstance()->getCachePath() . '/session';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                chmod($dir, 0755);
            }

            $path = $dir . '/' . $this->id;

            $expire = time() + $this->expire;
            $content = $expire . serialize($this->data);
            file_put_contents($path, $content);
        }
    }

    public function close()
    {
        $this->write();
        $this->data = null;
    }

    /**
     * 销毁 session
     *
     * @return bool
     */
    public function destroy()
    {
        $this->data = null;

        $path = RuntimeFactory::getInstance()->getCachePath() . '/session/' . $this->id;
        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

}
