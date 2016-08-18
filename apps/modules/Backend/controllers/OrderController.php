<?php

namespace Backend\Controllers;

class OrderController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Order'])) {
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
        $orderStatusModel = new \Backend\Models\OrderStatusModel();
        $orderModel = new \Backend\Models\OrderModel();
        $provinceModel = new \Backend\Models\ZoneProvinceModel();
        $this->view->province = $provinceModel::find();
        $this->view->data = $orderModel::find(array("os_id = 7", "order " => "or_create_date desc"));
        $this->view->order_status = $orderStatusModel::find();
        $this->view->setLayout("map");
        $this->view->header_title = "Order Manager";
    }

    public function orderDetailAction()
    {
        $id = $this->request->getQuery("id");
        $orderModel = new \Backend\Models\OrderModel();
        $orderHistoryModel = new \Backend\Models\OrderHistoryModel();
        $orderStatusModel = new \Backend\Models\OrderStatusModel();
        $this->view->category_status = $orderStatusModel::find();
        $this->view->order_history = $orderHistoryModel::find(array("or_id='{$id}'"));
        $this->view->data = $orderModel::findFirst($id);
        $this->view->setLayout("map");
        $this->view->header_title = "Order Detail Manager";
    }

    public function detailContent($input)
    {
        $dataModel = new \Backend\Models\OrderModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function searchAction()
    {
        if ($this->request->isPost()) {
            $respon['status'] = 0;
            $data = json_decode($this->request->getPost('data'), true);
            if ($data['os_id'] == '-1') {
                unset($data['os_id']);
            }
            $string = "";
            foreach ($data as $key => $val) {
                if ($val != '' && $key != 'from_date' && $key != 'to_date') {
                    $string .= $key . "='" . $val . "' and ";
                }
            }

            $string .= " or_create_date <= '" . $data['to_date'] . "' and '" . $data['from_date'] . "' <= or_create_date ";
            $dataModel = new \Backend\Models\OrderModel();
            $data = $dataModel::find(array($string));
            $this->view->data=$data;
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
        $buildingModel = new \Backend\Models\OrderModel();
        //update
        $data['or_edit_date'] = date("Y-m-d H:i:s");
        if ($buildingModel->updateByID($data, $data['or_id'])) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể update ';
        }
        return $respon;
    }

    public function addHistoryOrder($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        if ($data['os_id'] == '-1') {
            $respon['message'] = 'Vui lòng chọn Trạng Thái';
            return $respon;
        }

        $buildingModel = new \Backend\Models\OrderHistoryModel();
        $data['oh_time'] = date("Y-m-d H:i:s");
        if ($buildingModel->create($data)) {
            $orderModel = new \Backend\Models\OrderModel();
            $order = $orderModel::findFirst($data['or_id']);
            $order->os_id = $data['os_id'];
            $order->or_edit_date = date("Y-m-d H:i:d");
            $order->update();
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể update ';
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

    public function provinceZone($input)
    {
        $districtModel = new \Backend\Models\ZoneDistrictModel();
        $district = $districtModel::find(array("zp_id = '{$input['data']}'"));
        $respon['status'] = 1;
        $respon['data'] = $district->toArray();
        return $respon;
    }

    public function districtZone($input)
    {
        $districtModel = new \Backend\Models\ZoneWardModel();
        $district = $districtModel::find(array("zd_id = '{$input['data']}'"));
        $respon['status'] = 1;
        $respon['data'] = $district->toArray();
        return $respon;
    }

    public function printAction()
    {
        $id = $this->request->getQuery("id");
        $orderModel = new \Backend\Models\OrderModel();
        $this->view->data = $orderModel::findFirst($id);
        $this->view->header_title = "Order Detail Manager";
    }
}
