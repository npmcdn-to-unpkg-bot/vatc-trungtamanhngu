<?php

namespace HqEngine\HqHolder;

class HqHolder extends \Phalcon\Mvc\Controller {

    public $_module_config;

    public function prepare($module_config = null, $holder = null, $controller = null, $action = null)
    {
        $this->_module_config = $module_config;
        $this->view           = $view                 = new \Phalcon\Mvc\View();
        $view->disableLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        $view->disableLevel(\Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT);
        $module_directory     = $this->getModuleDirectory();
        $view->setViewsDir($module_directory . "/holders/" . $holder . '/Views/' . strtolower($controller));
//        $view->pick($module_directory . "/holders/" . strtolower($holder) . '/views/' . strtolower($controller) . '/' . $action);
        // run init function
        if (method_exists($this, 'initialize'))
        {
            $this->initialize();
        }
    }

    public function getModuleDirectory()
    {
        return $this->di->get('registry')->directories->modules . $this->_module_config->name;
    }

}
