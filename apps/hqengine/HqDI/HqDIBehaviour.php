<?php

namespace HqEngine\HqDI;

use Phalcon\DI;
use Phalcon\DiInterface;

trait HqDIBehaviour {

    public $_di;

    public function createDI($di = null)
    {
        if ($di == null)
        {
            $di = DI::getDefault();
        }
        $this->_di = $di;
    }

}
