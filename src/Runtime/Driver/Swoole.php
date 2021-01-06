<?php

namespace Be\Framework\Runtime\Driver;

use Be\Framework\Runtime\Driver;

/**
 * Swoole 运行时
 *
 * Class Swoole
 * @package Be\System\Runtime\Driver
 */
class Swoole extends Driver
{

    public function execute()
    {
        $httpServer = new HttpServer();
        $httpServer->start();
    }

}
