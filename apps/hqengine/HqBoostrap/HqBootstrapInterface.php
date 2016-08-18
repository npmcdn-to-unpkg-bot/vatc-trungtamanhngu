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

namespace HqEngine\HqBoostrap;

use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;

interface HqBootstrapInterface {

    /**
     * Create Bootstrap.
     *
     * @param DiInterface $di Dependency injection.
     * @param Manager     $em Events manager.
     */
    public function __construct($di, $em);

    /**
     * Register module services.
     *
     * @return void
     */
    public function registerServices();
    
}
