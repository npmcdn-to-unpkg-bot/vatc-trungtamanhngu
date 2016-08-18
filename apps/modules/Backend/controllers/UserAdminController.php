<?php

namespace Backend\Controllers;

class UserAdminController extends ControllerBase {

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
        $userAdminModel = new \Backend\Models\UserAdminModel();
        $this->view->setLayout("map");
        $this->view->data = $userAdminModel::find();
        $this->view->header_title = "User Model Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\UserAdminModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        $buildingModel = new \Backend\Models\UserAdminModel();
        if (empty($data['usa_id']) || $data['usa_id'] == NULL) {
            //insert
            unset($data['usa_id']);
            $check = $buildingModel::find(array("usa_username = '{$data['usa_username']}' or usa_email='{$data['usa_email']}'"));

            if (!empty($check->usa_id)) {
                $respon['message'] = 'Username hoặc Email đã tồn tại';
                return $respon;
            }
            $data['usa_password'] = md5($data['usa_password']);
            $data['usa_create_date'] = date("Y-m-d H:i:s");
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể update Animation';
            }
        } else {
            //update
            $checkPass = $buildingModel::findFirst(array("usa_id='{$data['usa_id']}'"));
            if ($checkPass->usa_password != $data['usa_password']) {
                $data['usa_password'] = md5($data['usa_password']);
            } else {
                unset($data['usa_password']);
            }
            if ($checkPass->update($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể update Animation';
            }
        }
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
