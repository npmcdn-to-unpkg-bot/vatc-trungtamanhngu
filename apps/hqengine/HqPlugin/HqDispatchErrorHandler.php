<?php

/*
  +------------------------------------------------------------------------+
  | PhalconEye CMS                                                         |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-2014 PhalconEye Team (http://phalconeye.com/)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconeye.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Ivan Vorontsov <ivan.vorontsov@phalconeye.com>                 |
  +------------------------------------------------------------------------+
 */

namespace HqEngine\HqPlugin;

use HqEngine\HqApplication\HqApplication as EngineApplication;
use HqEngine\HqException as EngineException;
use Phalcon\Dispatcher;
use Phalcon\Events\Event;
use Phalcon\Exception as PhalconException;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\User\Plugin as PhalconPlugin;

class HqDispatchErrorHandler extends PhalconPlugin {

    public function beforeException($event, $dispatcher, $exception)
    {
        $controller        = $dispatcher->getControllerName();
        $_array_controller = array("assets", "css", "js", "img", "icons", "swf");
        if (in_array(strtolower($controller), $_array_controller))
        {
            die;
        }
        // Handle 404 exceptions.
        if ($exception instanceof DispatchException)
        {
            $dispatcher->forward(
                    [
                        'controller' => 'Error',
                        'action'     => 'show404'
                    ]
            );
            return false;
        }

        if (APPLICATION_DEBUG)
        {
            throw $exception;
        }
        else
        {
            EngineException::logException($exception);
        }

        // Handle other exceptions.
        $dispatcher->forward(
                [
                    'controller' => 'Error',
                    'action'     => 'show500'
                ]
        );

        return $event->isStopped();
    }

}
