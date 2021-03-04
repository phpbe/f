<?php

namespace Be\F\Runtime;


class HttpServer
{
    /**
     * @var \Swoole\Http\Server
     */
    private $swooleHttpServer = null;


    public function __construct()
    {
    }


    public function start()
    {
    }


    public function stop()
    {
        $this->swooleHttpServer->stop();
    }

    public function reload()
    {
        $this->swooleHttpServer->reload();
    }

    public function getSwooleHttpServer()
    {
        return $this->swooleHttpServer;
    }

}
