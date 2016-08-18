<?php

namespace Frontend\Controllers;


use Backend\Models\ContactMessageModel;

class ContactController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
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

        $this->view->header_title = "Contact";
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
