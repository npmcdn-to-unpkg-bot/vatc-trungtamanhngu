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

namespace HqEngine\HqApplication;

use Phalcon\DI;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Application as PhalconApplication;
use Phalcon\Registry;

class HqApplication extends PhalconApplication {

    const SYSTEM_DEFAULT_MODULE = 'frontend';
    const SYSTEM_ERROR_MODULE = 'frontend';

    use HqApplicationInit;

    protected $_config;
    private $_loaders = [
        'normal' => [
            'environment',
            'cache',
            'memcache',
            'annotations',
            'router',
            'session',
            'memsession',
            'flash',
            'engine'
        ],
        'console' => [
            'environment',
            'database',
            'cache',
            'engine'
        ],
        'session' => [
            'cache',
            'database',
            'session'
        ],
    ];

    /**
     * Constructor.
     */
    public function __construct() {
        /**
         * Create default DI.
         */
        $di = new DI\FactoryDefault();

        /**
         * Get config.
         */
        $this->_config = \HqEngine\HqConfig::getConfig();

        /**
         * Setup Registry.
         */
        $registry = new Registry();
        $registry->modules = array_merge(
                [self::SYSTEM_DEFAULT_MODULE, 'frontend'], $this->_config->modules->toArray()
        );
        $registry->directories = (object) [
                    'config' => ROOT_PATH . '/apps/config/',
                    'engine' => ROOT_PATH . '/apps/hqengine/',
                    'modules' => ROOT_PATH . '/apps/modules/',
                    'plugins' => ROOT_PATH . '/apps/plugins/',
                    'widgets' => ROOT_PATH . '/apps/widgets/',
                    'libraries' => ROOT_PATH . '/apps/libraries/',
        ];

        $di->set('registry', $registry);

        // Store config in the DI container.
        $di->setShared('config', $this->_config);
        parent::__construct($di);
    }

    /**
     * Runs the application, performing all initializations.
     *
     * @param string $mode Mode name.
     *
     * @return void
     */
    public function run($mode = 'normal') {

        if (empty($this->_loaders[$mode])) {
            $mode = 'normal';
        }

        // Set application main objects.
        $di = $this->_dependencyInjector;
        $di->setShared('app', $this);
        $config = $this->_config;
        $eventsManager = new EventsManager();
        $this->setEventsManager($eventsManager);

        // Init base systems first.
        $this->_initLogger($di, $config);
        $this->_initLoader($di, $config, $eventsManager);

//        $this->_attachEngineEvents($eventsManager, $config);
        // Init services and engine system.

        foreach ($this->_loaders[$mode] as $service) {
            $serviceName = ucfirst($service);

            $eventsManager->fire('init:before' . $serviceName, null);
            $result = $this->{'_init' . $serviceName}($di, $config, $eventsManager);

            $eventsManager->fire('init:after' . $serviceName, $result);
        }
        $di->setShared('eventsManager', $eventsManager);
    }

    public function registerModules(array $modules, $merge = NULL) {
        $bootstraps = [];
        $di = $this->getDI();
        foreach ($modules as $moduleName => $moduleClass) {
            if (isset($this->_modules[$moduleName])) {
                continue;
            }

            $bootstrap = new $moduleClass($di, $this->getEventsManager());
            $bootstraps[$moduleName] = function () use ($bootstrap, $di) {
                $bootstrap->registerServices();
                return $bootstrap;
            };
        }
        return parent::registerModules($bootstraps, $merge);
    }

    /**
     * Get application output.
     *
     * @return string
     */
    public function getOutput() {
        return $this->handle()->getContent();
    }

    /**
     * Clear application cache.
     *
     * @param string $themeDirectory Theme directory.
     *
     * @return void
     */
    public function clearCache($themeDirectory = '') {
        $cacheOutput = $this->_dependencyInjector->get('cacheOutput');
        $cacheData = $this->_dependencyInjector->get('cacheData');
        $config = $this->_dependencyInjector->get('config');

        $cacheOutput->flush();
        $cacheData->flush();


        // Files deleter helper.
        $deleteFiles = function ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        };

        // Clear files cache.
        if (isset($config->cache->cacheDir)) {
            $deleteFiles(glob($config->cache->cacheDir . '*'));
        }

        // Clear view cache.
        $deleteFiles(glob($config->view->compiledPath . '*'));

        // Clear metadata cache.
        if ($config->metadata && $config->metadata->metaDataDir) {
            $deleteFiles(glob($config->metadata->metaDataDir . '*'));
        }

        // Clear annotations cache.
        if ($config->annotations && $config->annotations->annotationsDir) {
            $deleteFiles(glob($config->annotations->annotationsDir . '*'));
        }

        // Clear assets.
        $this->_dependencyInjector->getShared('assets')->clear(true, $themeDirectory);
    }

    /**
     * Check if application is used from console.
     *
     * @return bool
     */
    public function isConsole() {
        return (php_sapi_name() == 'cli');
    }

    /**
     * Attach required events.
     *
     * @param EventsManager $eventsManager Events manager object.
     * @param Config        $config        Application configuration.
     *
     * @return void
     */
    protected function _attachEngineEvents($eventsManager, $config) {
        // Attach modules plugins events.
        $events = $config->events->toArray();
        $cache = [];
        foreach ($events as $item) {
            list ($class, $event) = explode('=', $item);
            if (isset($cache[$class])) {
                $object = $cache[$class];
            } else {
                $object = new $class();
                $cache[$class] = $object;
            }
            $eventsManager->attach($event, $object);
        }
    }

}
