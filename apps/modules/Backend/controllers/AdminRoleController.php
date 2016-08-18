<?php

namespace Backend\Controllers;

class AdminRoleController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin'])) {
            return $this->response->redirect($this->config['rootUrl'] . $this->module_config_backend->main_route . "/");
        }
    }

    public function handleAction() {
        $output = [];
        if (method_exists($this, $_POST['method']))
            $output = $this->{$_POST['method']}($_POST);
        echo json_encode($output);
        die;
    }

    public function indexAction() {
        $adminRoleModel = new \Backend\Models\AdminRoleModel();
        $userAdminModel = new \Backend\Models\UserAdminModel();
        $listuser = $userAdminModel::find(array("columns" => "usa_username"));
        $listuser = $listuser->toArray();
        $tempArray = array();
        foreach ($listuser as $val) {
            $tempArray[] = $val['usa_username'];
        }
        $this->view->setLayout("map");
        $this->view->data = $adminRoleModel::find();
        $this->view->listuser = json_encode($tempArray);
        $this->view->header_title = "User Admin Role Manager";
    }

    public function newEditContent($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        foreach ($data as $key => $val) {
            $adminRoleModel = new \Backend\Models\AdminRoleModel();
            $data_update = array(
                "role_ar_user" => $val
            );
            $adminRoleModel->updateByID($data_update, $key);
        }
        $respon['status'] = 1;
        $respon['message'] = 'Thành Công';
        return $respon;
    }

    public function deleteContent($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $buildingModel = new \Backend\Models\UserAdminModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa Animation';
        }
        return $respon;
    }

}
