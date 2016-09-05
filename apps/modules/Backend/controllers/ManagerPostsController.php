<?php

namespace Backend\Controllers;

use Backend\Models\ManagerHlvModel;
use Backend\Models\ManagerPostsModel;
use Phalcon\Mvc\Model\Message;

class ManagerPostsController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Manager Posts'])) {
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
        $dataModel = new ManagerPostsModel();
        $dataCategoryModel = new ManagerHlvModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find();
        $this->view->category_data = $dataCategoryModel::find();
        $this->view->header_title = "Manager Posts";
    }

    public function detailContent($input)
    {
        $dataModel = new ManagerPostsModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;
        $validation = false;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        $buildingModel = new ManagerPostsModel();
        if (empty($data['p_id']) || $data['p_id'] == NULL) {
            //insert
            unset($data['p_id']);
            $buildingModel->create($data);
            $validation = $buildingModel->getMessages();
        } else {
            //update
            $update = $buildingModel::find($data['p_id']);
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
        $buildingModel = new ManagerPostsModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
