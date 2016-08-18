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

use HqEngine\HqAsset\HqAssetManager as AssetsManager;
use Phalcon\Annotations\Adapter\Memory as AnnotationsMemory;
//use Phalcon\Annotations\Adapter\Files as AnnotationsMemory;
use Phalcon\Cache\Frontend\Data as CacheData;
use Phalcon\Cache\Frontend\Output as CacheOutput;
use Phalcon\Cache\Backend\Memcache as MemCache;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Db\Profiler as DatabaseProfiler;
use Phalcon\DI;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Loader;
use Phalcon\Logger\Adapter\File;
use Phalcon\Logger;
use Phalcon\Logger\Formatter\Line as FormatterLine;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\MetaData\Strategy\Annotations as StrategyAnnotations;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Session\Adapter as SessionAdapter;
use Phalcon\Session\Adapter\Files as SessionFiles;
use Phalcon\Session\Adapter\Memcache as MemSession;

trait HqApplicationInit
{

    /**
     * Init logger.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initLogger($di, $config)
    {
        if ($config->logger->enabled) {
            $di->set(
                'logger', function ($file = 'main', $format = null) use ($config) {
                $logger = new File($config->logger->path . '.' . $file . '.log');
                $formatter = new FormatterLine(($format ? $format : $config->logger->format));
                $logger->setFormatter($formatter);

                return $logger;
            }, false
            );
        }
    }

    /**
     * Init loader.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     * @param EventsManager $eventsManager Event manager.
     *
     * @return Loader
     */
    protected function _initLoader($di, $config, $eventsManager)
    {
        // Add all required namespaces and modules.
        $registry = $di->get('registry');
        $namespaces = [];
        $bootstraps = [];
        foreach ($registry->modules as $module) {
            $moduleName = ucfirst($module);
            $namespaces[$moduleName] = $registry->directories->modules . $moduleName;
            $namespaces[$moduleName . "\Controllers"] = $registry->directories->modules . $moduleName . "/controllers";
            $namespaces[$moduleName . "\Models"] = $registry->directories->modules . $moduleName . "/models";
            $namespaces[$moduleName . "\Holders"] = $registry->directories->modules . $moduleName . "/holders";
            $bootstraps[$module] = $moduleName . '\Bootstrap';
        }
        $namespaces['HqEngine'] = $registry->directories->engine;
        $namespaces['HqPlugin'] = $registry->directories->plugins;
        $namespaces['HqWidget'] = $registry->directories->widgets;
        $namespaces['HqLibrary'] = $registry->directories->libraries;
        $loader = new Loader();
        $loader->registerNamespaces($namespaces);

        if (APPLICATION_DEBUG) {
            $loader->setEventsManager($eventsManager);
        }

        $loader->register();
        $this->registerModules($bootstraps);
        $di->set('loader', $loader);
    }

    /**
     * Init environment.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return Url
     */
    protected function _initEnvironment($di, $config)
    {
        set_error_handler(
            function ($errorCode, $errorMessage, $errorFile, $errorLine) {
                throw new \ErrorException($errorMessage, $errorCode, 1, $errorFile, $errorLine);
            }
        );

        set_exception_handler(
            function ($e) use ($di) {
                $errorId = Exception::logException($e);

                if ($di->get('app')->isConsole()) {
                    echo 'Error <' . $errorId . '>: ' . $e->getMessage();
                    return true;
                }

                return true;
            }
        );

        if ($config->profiler) {
            $profiler = new \HqEngine\HqDB\HqProfiler();
            $di->set('profiler', $profiler);
        }

        $url = new Url();
        $url->setBaseUri($config->baseUrl);
        $di->set('url', $url);

        return $url;
    }

    /**
     * Attach required events.
     *
     * @param EventsManager $eventsManager Events manager object.
     * @param Config $config Application configuration.
     *
     * @return void
     */
    protected function _attachHqEngineEvents($eventsManager, $config)
    {
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

    /**
     * Init annotations.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initAnnotations($di, $config)
    {
        $di->set(
            'annotations', function () use ($config) {
            if (APPLICATION_DEBUG && isset($config->annotations)) {
                $annotationsAdapter = '\Phalcon\Annotations\Adapter\\' . $config->annotations->adapter;
                $adapter = new $annotationsAdapter($config->annotations->toArray());
            } else {
                $adapter = new AnnotationsMemory();
            }

            return $adapter;
        }, true
        );
    }

    /**
     * Init router.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return Router
     */
    protected function _initRouter($di, $config)
    {

        // Load all controllers of all modules for routing system.
        $modules = $di->get('registry')->modules;
        $default_module = HqApplication::SYSTEM_DEFAULT_MODULE;
        if (isset($config->router->default_module)) {
            $default_module = $config->router->default_module;
        }
        $defaultModuleName = ucfirst($default_module);
        $router = new Router(true);
        $router->setDefaultModule($default_module);
        $router->setDefaultNamespace(ucfirst($default_module) . '\Controllers');
        $router->setDefaultController("Index");
        $router->setDefaultAction("index");


        foreach ($modules as $module) {
            $moduleName = ucfirst($module);
            $moduleClass = $moduleName . '\Controllers';
            // Get all file names.
            $router_file = $di->get('registry')->directories->modules . $moduleName . "/config/router.php";
            if (is_file($router_file)) {
                include_once $router_file;
            } else {
                $router->add('/' . $module . '/:params', array(
                    'namespace' => $moduleClass,
                    'module' => $module,
                    'controller' => 'Index',
                    'action' => 'index',
                    'params' => 1
                ))->setName($module);
                $router->add('/' . $module . '/:controller/:params', array(
                    'namespace' => $moduleClass,
                    'module' => $module,
                    'controller' => 1,
                    'action' => 'index',
                    'params' => 2
                ));
                $router->add('/' . $module . '/:controller/:action/:params', array(
                    'namespace' => $moduleClass,
                    'module' => $module,
                    'controller' => 1,
                    'action' => 2,
                    'params' => 3
                ))->convert(
                    'action', function ($action) {
                    return str_replace('-', '', $action);
                });
            }
        }
        $di->set('router', $router);
        return $router;
    }

    /**
     * Init session.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return SessionAdapter
     */
    protected function _initSession($di, $config)
    {
        if (!isset($config->session)) {
            $session = new SessionFiles();
        } else {
            $adapterClass = 'Phalcon\Session\Adapter\\' . $config->session->adapter;
            $session = new $adapterClass($config->session->toArray());
        }

        $session->start();
        $di->setShared('session', $session);
        return $session;
    }

    protected function _initMemsession($di, $config)
    {
        if (!isset($config->memsession)) {
            $session = new MemSession();
        } else {
            $adapterClass = 'Phalcon\Session\Adapter\\' . $config->memsession->adapter;
            $session = new $adapterClass($config->memsession->toArray());
        }

        $session->start();
        $di->setShared('memsession', $session);
        return $session;
    }

    /**
     * Init cache.
     *
     * @param DI $di Dependency Injection.
     * @param Config $config Config object.
     *
     * @return void
     */
    protected function _initCache($di, $config)
    {
        $cacheAdapter = '\Phalcon\Cache\Backend\\' . $config->cache->adapter;
        $frontEndOptions = ['lifetime' => $config->cache->lifetime];
        $backEndOptions = $config->cache->toArray();
        $frontOutputCache = new CacheOutput($frontEndOptions);
        $frontDataCache = new CacheData($frontEndOptions);


        $cacheOutputAdapter = new $cacheAdapter($frontOutputCache, $backEndOptions);
        $di->set('viewCache', $cacheOutputAdapter, true);
        $di->set('cacheOutput', $cacheOutputAdapter, true);

        $cacheDataAdapter = new $cacheAdapter($frontDataCache, $backEndOptions);
        $di->set('cacheData', $cacheDataAdapter, true);
        $di->set('modelsCache', $cacheDataAdapter, true);
    }

    protected function _initMemcache($di, $config)
    {
        $memCacheAdapter = '\Phalcon\Cache\Backend\\' . $config->memcache->adapter;
        $backEndMemOptions = $config->memcache->toArray();
        $frontEndMemOptions = ['lifetime' => $config->memcache->lifetime];
        $frontMemCache = new CacheData($frontEndMemOptions);

        $memCacheDataAdapter = new $memCacheAdapter($frontMemCache, $backEndMemOptions);
        $di->set('memcache', $memCacheDataAdapter, true);
    }

    /**
     * Init flash messages.
     *
     * @param DI $di Dependency Injection.
     *
     * @return void
     */
    protected function _initFlash($di)
    {
        $flashData = [
            'error' => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info',
        ];

        $di->set(
            'flash', function () use ($flashData) {
            $flash = new FlashDirect($flashData);

            return $flash;
        }
        );

        $di->set(
            'flashSession', function () use ($flashData) {
            $flash = new FlashSession($flashData);

            return $flash;
        }
        );
    }

    /**
     * Init engine.
     *
     * @param DI $di Dependency Injection.
     *
     * @return void
     */
    protected function _initEngine($di)
    {
        foreach ($di->get('registry')->modules as $module) {
            // Initialize module api.
            $di->setShared(
                strtolower($module), function () use ($module, $di) {
                //    return new ApiInjector($module, $di);
            }
            );
        }

        $di->setShared(
            'transactions', function () {
            return new TxManager();
        }
        );
        $di->setShared('assets', new AssetsManager($di));
        // $di->setShared('widgets', new Catalog());
    }
//
//    protected function _initDatabase()
//    {
////        $di = $this->_di;
////        $config = $this->_config;
////        $eventsManager = $this->_em;
//        $module_config = $this->_module_config;
//        $adapter = '\Phalcon\Db\Adapter\Pdo\\' . $module_config->database->adapter;
//        /** @var Pdo $connection */
//        $connection = new $adapter(
//            [
//                "host" => $module_config->database->host,
//                "port" => $module_config->database->port,
//                "username" => $module_config->database->username,
//                "password" => $module_config->database->password,
//                "dbname" => $module_config->database->dbname,
//                "options" => array(
//                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
//                )
//            ]
//        );
//
//        $isDebug = APPLICATION_DEBUG;
//        $isProfiler = $config->profiler;
//        if ($isDebug || $isProfiler) {
//            // Attach logger & profiler.
//            $logger = null;
//            $profiler = null;
//
//            if ($isDebug) {
//                $logger = new \Phalcon\Logger\Adapter\File($config->logger->path . "/db/db_" . date("Y_m_d") . ".log");
//            }
//            if ($isProfiler) {
//                $profiler = new DatabaseProfiler();
//            }
//
//            $eventsManager->attach(
//                'db', function ($event, $connection) use ($logger, $profiler) {
//                if ($event->getType() == 'beforeQuery') {
//                    $statement = $connection->getSQLStatement();
//                    if ($logger) {
//                        $logger->log($statement, Logger::INFO);
//                    }
//                    if ($profiler) {
//                        $profiler->startProfile($statement);
//                    }
//                }
//                if ($event->getType() == 'afterQuery') {
//                    // Stop the active profile.
//                    if ($profiler) {
//                        $profiler->stopProfile();
//                    }
//                }
//            }
//            );
//
//            if ($profiler && $di->has('profiler')) {
//                $di->get('profiler')->setDbProfiler($profiler);
//            }
//            $connection->setEventsManager($eventsManager);
//        }
//
//        $di->set('db', $connection);
//        $di->set(
//            'modelsManager', function () use ($config, $eventsManager) {
//            $modelsManager = new \Phalcon\Mvc\Model\Manager();
//            $modelsManager->setEventsManager($eventsManager);
//
//            return $modelsManager;
//        }, true
//        );
//
////        /**
////         * If the configuration specify the use of metadata adapter use it or use memory otherwise.
////         */
////        $di->set(
////                'modelsMetadata', function () use ($config) {
////            if (!APPLICATION_DEBUG && isset($config->metadata))
////            {
////                $metaDataConfig  = $config->metadata;
////                $metadataAdapter = '\Phalcon\Mvc\Model\Metadata\\' . $metaDataConfig->adapter;
////                $metaData        = new $metadataAdapter($config->metadata->toArray());
////            }
////            else
////            {
////                $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
////            }
////
////            $metaData->setStrategy(new \Phalcon\Mvc\Model\MetaData\Strategy\Annotations());
////
////            return $metaData;
////        }, true
////        );
//        return $connection;
//    }
}
