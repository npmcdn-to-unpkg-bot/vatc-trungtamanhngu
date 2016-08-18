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

namespace Frontend;

use HqEngine\Cache\System;
use HqEngine\HqConfig as Config;
use HqEngine\Translation\Db as TranslationDb;
use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\View;

class Bootstrap extends \HqEngine\HqBoostrap\HqBootstrap {

    /**
     * Current module name.
     *
     * @var string
     */
    public $_moduleName = "Frontend";

    /**
     * Bootstrap construction.
     *
     * @param DiInterface $di Dependency injection.
     * @param Manager     $em Events manager object.
     */
    public function __construct($di, $em)
    {
        parent::__construct($di, $em);

        /**
         * Attach this bootstrap for all application initialization events.
         */
    }

    /**
     * Init some subsystems after engine initialization.
     */
    public function afterEngine()
    {
        $di     = $this->_di;
        $config = $this->_config;
        // Init widgets system.
        // $this->_initWidgets($di);

        /**
         * Listening to events in the dispatcher using the Acl.
         */
        if ($config->acl)
        {
            //  $this->_em->attach('dispatch', $di->get('core')->acl());
        }
    }

    /**
     * Prepare widgets metadata for Engine.
     *
     * @param DI $di Dependency injection.
     *
     * @return void
     */
    protected function _initWidgets(DI $di)
    {
        if ($di->get('app')->isConsole())
        {
            return;
        }

        $cache   = $di->get('cacheData');
        $widgets = $cache->get(System::CACHE_KEY_WIDGETS_METADATA);

        if ($widgets === null)
        {
            $widgets = [];
            foreach (Widget::find() as $object)
            {
                $widgets[] = [$object->id, $object->getKey(), $object];
            }

            $cache->save(System::CACHE_KEY_WIDGETS_METADATA, $widgets, 0); // Unlimited.
        }
        $di->get('widgets')->addWidgets($widgets);
    }

}
