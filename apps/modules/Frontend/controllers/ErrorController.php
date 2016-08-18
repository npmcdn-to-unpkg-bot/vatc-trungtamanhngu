<?php

namespace Frontend\Controllers;

class ErrorController extends ControllerBase
{
    public function indexAction()
    {
        echo 456;
        die;
    }

    public function show404Action()
    {
//        return $this->response->redirect("");
        echo "404 Not Found";
        die;
    }

    public function show500Action()
    {

    }

}
