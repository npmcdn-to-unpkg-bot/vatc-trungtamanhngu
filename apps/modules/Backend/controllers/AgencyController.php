<?php

namespace Backend\Controllers;

class AgencyController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Product'])) {
            return $this->response->redirect($this->config['rootUrl'] . $this->module_config_backend->main_route . "/");
        }
    }

    public function handleAction()
    {
        $output = [];
        if (method_exists($this, $this->request->getPost('method')))
            $output = $this->{$this->request->getPost('method')}($this->request->getPost());
        echo json_encode($output);
        die;
    }

    public function indexAction()
    {
        $dataModel = new \Backend\Models\AgencyModel();
        $provinceModel = new \Backend\Models\ZoneProvinceModel();
        $this->view->province = $provinceModel::find();
        $this->view->data = $dataModel::find();
        $this->view->header_title = "Agency Manager";
        $this->view->setLayout("map");
    }

    public function detailContent($input)
    {
        $dataModel = new \Backend\Models\AgencyModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input)
    {

        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);

        $buildingModel = new \Backend\Models\AgencyModel();
        if (empty($data['ag_id']) || $data['ag_id'] == NULL) {
            //insert
            unset($data['ag_id']);
            $buildingModel->create($data);
            $validation = $buildingModel->getMessages();
        } else {
            //update
            $update = $buildingModel::find($data['ag_id']);
            $update->update($data);
            $validation = $update->getMessages();
        }
        //Validation
        if (empty($validation) || is_null($validation)) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';

        } else {
            $text = '';
            foreach ($validation as $message) {
                $text .= $message . "\n";
            }
            $respon['message'] = $text;
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
        $buildingModel = new \Backend\Models\AgencyModel();

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
