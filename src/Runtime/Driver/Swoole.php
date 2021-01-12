<?php

namespace Be\F\Runtime\Driver;

use Be\F\Runtime\Driver;

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
