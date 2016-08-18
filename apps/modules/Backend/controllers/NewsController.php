<?php

namespace Backend\Controllers;

class NewsController extends ControllerBase {

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
        $dataModel = new \Backend\Models\NewsModel();
        $dataCategoryModel = new \Backend\Models\NewsCategoryModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find(array("order" => "n_id desc"));
        $this->view->category_data = $dataCategoryModel::find();
        $this->view->header_title = "News Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\NewsModel();
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
        $buildingModel = new \Backend\Models\NewsModel();
        //Validation
        $validation = $buildingModel->validationRequest($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation

        $data['n_status'] = isset($data['n_status']) ? 1 : 0;
        if ($this->get_seo($data['n_name']) != $data['n_seo_link']) {
            $data['n_seo_link'] = $this->get_seo($data['n_name']);
        }
        $data['n_description'] = $data['description'];
        unset($data['description']);

        if (empty($data['n_id']) || $data['n_id'] == NULL) {
            //insert
            unset($data['n_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['n_id']);
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
        $buildingModel = new \Backend\Models\NewsModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
