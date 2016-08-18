<?php

namespace Backend\Controllers;

use Backend\Models\CouponModel;
use Phalcon\Mvc\Model\Message;

class CouponController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Coupon'])) {
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
        $dataModel = new CouponModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find();
        $this->view->header_title = "Coupon Manager";
    }

    public function detailContent($input)
    {
        $dataModel = new CouponModel();
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
        $buildingModel = new CouponModel();
        $data['co_status'] = isset($data['co_status']) ? 1 : 0;
        if ($data['co_type'] == 1 && $data['co_discount'] > 100) {
            $respon['message'] = 'Discount Không thể lớn hơn 100%';
            return $respon;
        }
        if (empty($data['co_id']) || $data['co_id'] == NULL) {
            //insert
            unset($data['co_id']);
            $buildingModel->create($data);
            $validation = $buildingModel->getMessages();
        } else {
            //update
            $update = $buildingModel::find($data['co_id']);
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
        $buildingModel = new CouponModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
