<?php

namespace Backend\Controllers;

class UserController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['User'])) {
            return $this->response->redirect($this->config['rootUrl'] . $this->module_config_backend->main_route . "/");
        }
    }

    public function handleAction()
    {
        $output = [];
        if (method_exists($this, $_POST['method']))
            $output = $this->{$_POST['method']}($_POST);
        echo json_encode($output);
        die;
    }

    public function indexAction()
    {
        $this->view->setLayout("map");
        $this->view->header_title = "User Manager";
    }

    public function detailContent($input)
    {
        $dataModel = new \Backend\Models\UserModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function searchAction()
    {
        if ($this->request->isPost()) {
            $respon['status'] = 0;
            $data = json_decode($this->request->getPost('data'), true);
            $string = '';
            foreach ($data as $key => $val) {
                if ($val != '') {
                    $string .= $key . " like '%" . $val . "%' and ";
                }
            }
            $string = substr($string, 0, -4);
            $dataModel = new \Backend\Models\UserModel();
            $user = $dataModel::find(array($string));
            $this->view->data = $user;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);

        $buildingModel = new \Backend\Models\UserModel();
        $user = $buildingModel::findFirst($data['us_id']);
        if (isset($data['us_email']) && $user->us_email != $data['us_email']) {
            //Validation
            $validation = $buildingModel::findFirst("us_email = '{$data['us_email']}'");
            if ($validation) {
                $respon['message'] = "Email đã tồn tại";
                return $respon;
            }
            //End Validation
        }

        $data['us_status'] = isset($data['us_status']) ? 1 : 0;
        if ($user->update($data)) {
            $respon['status'] = 1;
            $respon['message'] = 'Cập nhật thông tin thành công';
        } else {
            $respon['message'] = 'Không thể Thêm';
        }
        return $respon;
    }

    public function deleteContent($input)
    {
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
