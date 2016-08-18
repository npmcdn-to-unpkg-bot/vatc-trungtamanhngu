<?php

namespace Backend\Controllers;

class AccessController extends ControllerBase {

    public function handleAction() {
        $output = [];
        if (method_exists($this, $_POST['method']))
            $output = $this->{$_POST['method']}($_POST);
        echo json_encode($output);
        die;
    }

    public function indexAction() {
        $this->view->header_title = "Login ";
    }

    public function login($input) {
        $respon['status'] = 0;
        $email = FALSE;
        $data = json_decode($input['data'], true);
        if (empty($data) || empty($data['username']) || empty($data['password'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        if (strlen($data['username']) < 5 || !$this->checkSpecialChar($data['username'])) {
            $result['message'] = 'Tên đăng nhập không chứa ký tự đặc biệt';
            return $result;
        }
        
        if (filter_var($data['username'], FILTER_VALIDATE_EMAIL)) {
            $email = TRUE;
        }
        $userAdminModel = new \Backend\Models\UserAdminModel();
        if ($email) {
            $user = $userAdminModel::findFirst(array(
                        'usa_email = :userEmail:  
                    AND usa_password = :userPassword:',
                        'bind' => array(
                            'userEmail' => $data['username'],
                            'userPassword' => md5($data['password']),
            )));
        } else {
            $user = $userAdminModel::findFirst(array(
                        'usa_username = :userEmail:  
                    AND usa_password = :userPassword:',
                        'bind' => array(
                            'userEmail' => $data['username'],
                            'userPassword' => md5($data['password']),
            )));
        }

        if (empty($user)) {
            $respon['message'] = "Đăng nhập không thành công";
        } else {
            $user->usa_last_login = date("Y-m-d H:i:s");
            $user->save();
            $this->createSession($user->toArray());
            $respon['status'] = 1;
            $respon['message'] = "Đăng nhập thành công";
        }
        return $respon;
    }

    public function checkSpecialChar($value) {
        $stringWithout = preg_match('/[^a-zA-Z0-9]/', $value, $stringWith);

        if ($stringWithout == 0 || filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {

            return false;
        }
    }
    public function logoutAction() {
        $this->destroySession();
        return $this->response->redirect($this->urlModule . "access");
    }

}
