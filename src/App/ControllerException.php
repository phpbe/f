<?php
namespace Be\Framework\App;

use Throwable;

/**
 * 控制器异常
 */
class ControllerException extends \Exception
{

    private $redirectUrl = null;

    public function __construct($message = "", $code = 0, $redirectUrl = null)
    {
        $this->redirectUrl = $redirectUrl;

        parent::__construct($message, $code);
    }

}
