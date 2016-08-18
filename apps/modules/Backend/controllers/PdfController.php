<?php

namespace Backend\Controllers;

class PdfController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) ) {
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
        echo "<pre>";
        print_r(1111);
        echo "</pre>";
        die;
        $this->view->setLayout("map");
        $this->view->header_title = "User Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\UserModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function search($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        $string = '';
        foreach ($data as $key => $val) {
            if ($val != '') {
                $string.=$key . "='" . $val . "' and ";
            }
        }
        if ($string == '') {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $string = substr($string, 0, -4);
        $dataModel = new \Backend\Models\UserModel();
        $user = $dataModel::find(array($string));
        $user = $user->toArray();
        if (!empty($user)) {
            $respon['status'] = 1;
            $respon['data'] = $user;
        } else {
            $respon['status'] = 0;
            $respon['message'] = 'Không có dữ liệu';
        }
        return $respon;
    }

    public function newEditContent($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        $buildingModel = new \Backend\Models\UserModel();
        $check = $buildingModel::find(array("(us_name = '{$data['us_name']}' and us_id!='{$data['us_id']}') or ( us_email='{$data['us_email']}' and us_id!='{$data['us_id']}')"));
        $check = $check->toArray();
        if (!empty($check)) {
            $respon['message'] = 'Username hoặc Email đã tồn tại';
            return $respon;
        }
        //update
        $data['us_status'] = isset($data['us_status']) ? 1 : 0;
        $data['us_newsletter'] = isset($data['us_newsletter']) ? 1 : 0;
        $checkPass = $buildingModel::findFirst(array("us_id='{$data['us_id']}'"));
        if ($checkPass->us_password != $data['us_password']) {
            $data['us_password'] = md5($data['us_password']);
        } else {
            unset($data['us_password']);
        }
        if ($buildingModel->updateByID($data, $data['us_id'])) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể update ';
        }
        return $respon;
    }

    public function deleteContent($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $buildingModel = new \Backend\Models\UserModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa Animation';
        }
        return $respon;
    }

}
