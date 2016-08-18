<?php

namespace Backend\Controllers;

class BannerController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Banner'])) {
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
        $dataModel = new \Backend\Models\BannerModel();
        $dataCategoryModel = new \Backend\Models\BannerCategoryModel();
        $this->view->setLayout("map");
        if($this->request->isPost()){
            $bc_id=$this->request->getPost('bc_id');
            $this->view->data_search=$bc_id;
            $this->view->data = $dataModel::find(array("bc_id = '{$bc_id}'"));
        }else{
            $this->view->data = $dataModel::find();
        }

        $this->view->category_data = $dataCategoryModel::find();
        $this->view->header_title = "Banner Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\BannerModel();
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
        if (!isset($data['bc_id']) || $data['bc_id'] == '-1' || empty($data['ba_image_link'])) {
            $respon['message'] = 'Không được để trống Category hoặc Image';
            return $respon;
        }
        $buildingModel = new \Backend\Models\BannerModel();
        $check_title = $buildingModel::findFirst(array("ba_name='{$data['ba_name']}' and ba_id!='{$data['ba_id']}'"));
        if ($check_title) {
            $respon['message'] = 'Title đã tồn tại';
            return $respon;
        }
        if (empty($data['ba_id']) || $data['ba_id'] == NULL) {
            //insert
            unset($data['ba_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['ba_id']);
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
        $buildingModel = new \Backend\Models\BannerModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
