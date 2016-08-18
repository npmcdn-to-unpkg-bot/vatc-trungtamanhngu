<?php

namespace Backend\Controllers;

use Backend\Models\UserModel;

class IndexController extends ControllerBase {

    public function indexAction() {
        $orderModel = new UserModel();
        $this->view->year = $orderModel::find(array("columns" => array("time" => "DISTINCT(YEAR(created_at))")));
        $this->view->header_title = "Backend Admin";
    }


}
