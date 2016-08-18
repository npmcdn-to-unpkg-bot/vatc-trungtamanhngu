<?php

namespace HqEngine\HqBoostrap;

use HqEngine\HqPlugin\HqDispatchErrorHandler as DispatchErrorHandler;
use Phalcon\Config as PhalconConfig;
use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager;
use Phalcon\Logger;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Db\Profiler as DatabaseProfiler;

abstract class HqBootstrap implements HqBootstrapInterface {

    public $_moduleName = "";
    public $_config;
    public $_em;
    public $_module_config;

    use \HqEngine\HqDI\HqDIBehaviour;

    /**
     * Create Bootstrap.
     *
     * @param DiInterface $di Dependency injection.
     * @param Manager     $em Events manager.
     */
    public function __construct($di, $em)
    {
        $this->createDI($di);
        $this->_config        = $this->_di->get('config');
        $this->_em            = $em;
        $moduleDirectory      = $this->getModuleDirectory();
        $this->_module_config = include_once $moduleDirectory . "/config/config.php";

        $di->setShared("module_config_" . strtolower($this->_module_config->name), $this->_module_config);
        $serviceName          = ucfirst("database");
    }

    /**
     * Register the services.
     *
     * @throws Exception
     * @return void
     */
    public function registerServices()
    {
        if (empty($this->_moduleName))
        {
            $class = new \ReflectionClass($this);
            throw new Exception('Bootstrap has no module name: ' . $class->getFileName());
        }

        $di              = $this->_di;
        $config          = $this->_config;
        $eventsManager   = $this->_em;
        $moduleDirectory = $this->getModuleDirectory();
        /*         * ********************************************** */
        //  Initialize view.
        /*         * ********************************************** */
        $dispatcher      = new \HqEngine\HqDispatcher();
        $eventsManager->attach('dispatch:beforeDispatch', function ($event, $dispatcher) {
            $dispatcher->setControllerName(strtolower($dispatcher->getControllerName()));
        });
        $di->set(
                'view', function () use ($di, $config, $moduleDirectory, $eventsManager) {
            return \HqEngine\HqView\HqView::factory($di, $config, $moduleDirectory . '/views/', $eventsManager);
        }
        );

        /*         * ********************************************** */
        //  Initialize dispatcher.
        /*         * ********************************************** */
        $eventsManager->attach("dispatch:beforeException", new DispatchErrorHandler());
        // Create dispatcher.
        $dispatcher->setEventsManager($eventsManager);
        $di->set('dispatcher', $dispatcher);
        $this->_initDatabase();
        $this->_initLanguage($di, $config);
        $this->_initHolder();
    }

    protected function _initLanguage(DI $di, PhalconConfig $config)
    {
        $session = $di->get('session');
//        if (!$session->has('language'))
//        {
        if (isset($config->languages->language) && isset($config->languages->locale))
        {
            $session->set('language', $config->languages->language);
            $session->set('locale', $config->languages->locale);
        }
        else
        {
            $session->set('language', Config::CONFIG_DEFAULT_LANGUAGE);
            $session->set('locale', Config::CONFIG_DEFAULT_LOCALE);
        }
//        }
        $module_config = $this->_module_config;
        $di->set(
                'language', function () use ($di, $module_config) {
            $lanuage_class = new \HqEngine\HqLanguage\HqLanguage($di, $module_config);
            return $lanuage_class;
        }
        );
    }

    protected function _initHolder()
    {
        $di            = $this->_di;
        $config        = $this->_config;
        $eventsManager = $this->_em;
        $module_config = $this->_module_config;

        $di->set(
                'holders', function () use ($di, $module_config) {
            $holders = new \HqEngine\HqHolder\HqHolderCollection($di, $module_config);
            return $holders;
        }
        );
    }

    protected function _initDatabase()
    {
        $di            = $this->_di;
        $config        = $this->_config;
        $eventsManager = $this->_em;
        $module_config = $this->_module_config;
        $adapter       = '\Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
        /** @var Pdo $connection */
        $connection    = new $adapter(
                [
            "host"     => $config->database->host,
            "port"     => $config->database->port,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname"   => $config->database->dbname,
        "options" => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
                ]
        );

        $isDebug    = APPLICATION_DEBUG;
        $isProfiler = $config->profiler;
        if ($isDebug || $isProfiler)
        {
            // Attach logger & profiler.
            $logger   = null;
            $profiler = null;

            if ($isDebug)
            {
                $logger = new \Phalcon\Logger\Adapter\File($config->logger->path . "/db/db_" . date("Y_m_d") . ".log");
            }
            if ($isProfiler)
            {
                $profiler = new DatabaseProfiler();
            }

            $eventsManager->attach(
                    'db', function ($event, $connection) use ($logger, $profiler) {
                if ($event->getType() == 'beforeQuery')
                {
                    $statement = $connection->getSQLStatement();
                    if ($logger)
                    {
                        $logger->log($statement, Logger::INFO);
                    }
                    if ($profiler)
                    {
                        $profiler->startProfile($statement);
                    }
                }
                if ($event->getType() == 'afterQuery')
                {
                    // Stop the active profile.
                    if ($profiler)
                    {
                        $profiler->stopProfile();
                    }
                }
            }
            );

            if ($profiler && $di->has('profiler'))
            {
                $di->get('profiler')->setDbProfiler($profiler);
            }
            $connection->setEventsManager($eventsManager);
        }

        $di->set('db', $connection);
        $di->set(
                'modelsManager', function () use ($config, $eventsManager) {
            $modelsManager = new \Phalcon\Mvc\Model\Manager();
            $modelsManager->setEventsManager($eventsManager);

            return $modelsManager;
        }, true
        );

//        /**
//         * If the configuration specify the use of metadata adapter use it or use memory otherwise.
//         */
//        $di->set(
//                'modelsMetadata', function () use ($config) {
//            if (!APPLICATION_DEBUG && isset($config->metadata))
//            {
//                $metaDataConfig  = $config->metadata;
//                $metadataAdapter = '\Phalcon\Mvc\Model\Metadata\\' . $metaDataConfig->adapter;
//                $metaData        = new $metadataAdapter($config->metadata->toArray());
//            }
//            else
//            {
//                $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
//            }
//
//            $metaData->setStrategy(new \Phalcon\Mvc\Model\MetaData\Strategy\Annotations());
//
//            return $metaData;
//        }, true
//        );
        return $connection;
    }

    /**
     * Get current module directory.
     *
     * @return string
     */
    public function getModuleDirectory()
    {
        return $this->_di->get('registry')->directories->modules . $this->_moduleName;
    }

}
