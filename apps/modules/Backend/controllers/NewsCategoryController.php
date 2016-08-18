<?php

namespace Backend\Controllers;

class NewsCategoryController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['News'])) {
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
        $dataModel = new \Backend\Models\NewsCategoryModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find();
        $this->view->header_title = "News Category Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\NewsCategoryModel();
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
        $data['nc_status'] = isset($data['nc_status']) ? 1 : 0;
        $buildingModel = new \Backend\Models\NewsCategoryModel();
        $check_title = $buildingModel::findFirst(array("nc_name='{$data['nc_name']}' and nc_id!='{$data['nc_id']}'"));
        if ($check_title) {
            $respon['message'] = 'Title đã tồn tại';
            return $respon;
        }
        if (empty($data['nc_id']) || $data['nc_id'] == NULL) {
            //insert
            unset($data['nc_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['nc_id']);
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
        $buildingModel = new \Backend\Models\NewsCategoryModel();
        $bannerModel = new \Backend\Models\NewsModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $bannerModel::find(array("nc_id='{$input['data']}'"))->delete();
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa Animation';
        }
        return $respon;
    }

}
