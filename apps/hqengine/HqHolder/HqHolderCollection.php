<?php

namespace HqEngine\HqHolder;

class HqHolderCollection {

    protected $di;
    protected $module_config;

    public function __construct($di, $module_config)
    {
        $this->di            = $di;
        $this->module_config = $module_config;
    }

    public function load($holder = "", $controller = "index", $action = "index", $params = array())
    {
        if (!empty($holder))
        {
            $class_holder = $this->module_config->name . "\\Holders\\" . $holder . "\\Controllers\\" . $controller . "Controller";
            if (class_exists($class_holder))
            {
                $holder_controller = new $class_holder();
                $holder_controller->setDI($this->di);
                $function          = $action . "Action";
                $has_profiler      = $this->di->has('profiler');
                if ($has_profiler)
                {
                    $this->di->get('profiler')->start();
                }
                $holder_controller->prepare($this->module_config, $holder, $controller, $action);
                $holder_controller->$function();
                if ($has_profiler)
                {
                    $this->di->get('profiler')->stop($class_holder, 'holder');
                }
                ob_start();
                $holder_controller->view->render('', $action);
                $output = ob_get_contents();
                ob_end_clean();
                echo $output;
            }
            else
            {
                echo "Does not Exist Holder";
            }
        }
    }

}
