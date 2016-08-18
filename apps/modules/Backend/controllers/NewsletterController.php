<?php

namespace Backend\Controllers;

require $_SERVER['DOCUMENT_ROOT'] . '/apps/libraries/PHPMailer/PHPMailerAutoload.php';

class NewsletterController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['User'])) {
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
        $dataModel = new \Backend\Models\NewsletterModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find(array("order" => "created_at desc"));
        $this->view->header_title = "Newsletter Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\NewsletterModel();
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

        $data['ne_content'] = $data['description'];
        unset($data['description']);

        $buildingModel = new \Backend\Models\NewsletterModel();
        $check_title = $buildingModel::findFirst(array("ne_subject='{$data['ne_subject']}' and ne_id!='{$data['ne_id']}'"));
        if ($check_title) {
            $respon['message'] = 'Subject đã tồn tại';
            return $respon;
        }
        if (empty($data['ne_id']) || $data['ne_id'] == NULL) {
            //insert
            unset($data['ne_id']);
            $data['usa_id'] = $this->user['usa_id'];
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['ne_id']);
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
        $buildingModel = new \Backend\Models\NewsletterModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

    public function sendNewsletter($input) {
        $dataModel = new \Backend\Models\NewsletterModel();
        $userModel = new \Backend\Models\UserModel();
        $user_newsletter = $userModel::find(array("us_newsletter=1"));
        $data = $dataModel::findFirst($input['data']);
        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Host = 'smtp.zoho.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'no_reply@amhactrutien.com';                 // SMTP username
        $mail->Password = 'amhac231@#!';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;
        $mail->setFrom('no_reply@amhactrutien.com', 'Ninomax');
        foreach ($user_newsletter as $user) {
            $mail->addAddress($user->us_email, $user->us_full_name);
        }
        $mail->Subject = $data->ne_subject;
        $mail->Body = $data->ne_content;
        $mail->send();
        if (!$mail->send()) {
            $respon['message'] = 'Không thể gửi mail';
        } else {
            $respon['status'] = 1;
            $respon['message'] = 'Đã gửi Email thành công đến ' . count($user_newsletter) . ' customer';
        }
        return $respon;
    }

}
