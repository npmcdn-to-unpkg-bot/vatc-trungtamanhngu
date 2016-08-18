<?php

namespace Backend\Controllers;


use Backend\Models\InformationModel;

class InformationController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Information'])) {
            return $this->response->redirect($this->config['rootUrl'] . $this->module_config_backend->main_route . "/");
        }
    }

    public function handleAction() {
        $output = [];
        if (method_exists($this, $this->request->getPost('method')))
            $output = $this->{$this->request->getPost('method')}($this->request->getPost());
        echo json_encode($output);
        die;
    }

    public function indexAction() {
        $dataModel = new InformationModel();
        $this->view->data = $dataModel::find();
        $this->view->header_title = "Infomation Manager";
        $this->view->setLayout("map");
    }

    public function detailContent($input) {
        $dataModel = new InformationModel();
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

        $buildingModel = new InformationModel();
        if (empty($data['inf_id']) || $data['inf_id'] == NULL) {
            //insert
            unset($data['inf_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['inf_id']);
            if ($update->update($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Update';
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
        $buildingModel = new InformationModel();

        $data = $buildingModel::findFirst($input['data']);
        if ($data->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
