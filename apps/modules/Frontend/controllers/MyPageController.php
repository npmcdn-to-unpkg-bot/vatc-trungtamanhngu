<?php

namespace Frontend\Controllers;


use Backend\Models\OrderModel;
use Backend\Models\ProductModel;
use Backend\Models\ZoneDistrictModel;
use Backend\Models\ZoneProvinceModel;
use Backend\Models\ZoneWardModel;

class MyPageController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        if (empty($this->user)) {
            return $this->response->redirect('');
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
        $provinceModel = new ZoneProvinceModel();
        $districtModel = new ZoneDistrictModel();
        $wardModel = new ZoneWardModel();
        $this->view->province = $provinceModel::find(array("order" => "zp_name asc"));
        $this->view->district = $districtModel::find();
        $this->view->ward = $wardModel::find();
        $this->setScript();
        $this->view->header_title = "My Page";
    }

    public function changePasswordAction()
    {
        $this->setScript();
        $this->view->header_title = "My Page";
    }

    public function orderAction()
    {
        $orderModel = new OrderModel();
        $orderStatusModel = new \Backend\Models\OrderStatusModel();
        $this->view->order_status = $orderStatusModel::find();
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $where = 'us_id ='.$this->user->us_id;
            foreach ($data as $key => $val) {
                if (empty($val) || $val == '-1') {
                    unset($data[$key]);
                } else {
                    if ($key == 'date_from') {
                        $where .= " and DATE(or_create_date) >= '" . date("Y-m-d", strtotime($val))."'";
                    } elseif ($key == 'date_to') {
                        $where .= " and DATE(or_create_date) <= '" . date("Y-m-d", strtotime($val))."'";
                    }else{
                        $where .=" and ". $key . "='" . $val."'" ;
                    }
                    unset($data[$key]);
                }
            }
            $this->view->order = $orderModel::find(array($where));

        }else{
            $this->view->order = $orderModel::find(array("us_id = '{$this->user->us_id}'", "order" => "or_create_date desc"));
        }
        $this->setScript();
        $this->view->header_title = "My Page";
    }

    protected function setScript()
    {
        $this->assets->addCss('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', false);
        $this->assets->addCss('public/FrontendCore/css/mypage.css', true);
    }

    public function contactMessage($request)
    {

        $respon['status'] = 0;
        if (empty($request['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($request['data'], true);
        $userModel = new ContactMessageModel();
        //Validation
        $validation = $userModel->validationMessage($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation

        if ($userModel->create($data)) {
            $respon['status'] = 1;
            $respon['message'] = 'Message của bạn đã được gửi tới Admin.Xin chân thành cám ơn.';
        } else {
            $respon['message'] = 'Không thể Thêm';
        }
        return $respon;
    }

}
