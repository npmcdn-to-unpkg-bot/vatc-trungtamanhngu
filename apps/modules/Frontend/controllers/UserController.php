<?php

namespace Frontend\Controllers;

use Backend\Models\UserModel;

require $_SERVER['DOCUMENT_ROOT'] . '/apps/libraries/Facebook/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/apps/libraries/Google/autoload.php';
//require $_SERVER['DOCUMENT_ROOT'] . '/apps/libraries/PHPMailer/PHPMailerAutoload.php';

class UserController extends ControllerBase
{

    private $_reset_key = "$!^*980@&ajADNj1";

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
        $this->setScript();
        $this->view->header_title = "Đăng Nhập";
    }

    public function registerAction()
    {
        $this->setScript();
        $this->view->header_title = "Đăng Ký";
    }

    public function registerNewsletter($request)
    {

        $respon['status'] = 0;
        if (empty($request['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($request['data'], true);
        $userModel = new \Backend\Models\UserModel();
        //Validation
        $validation = $userModel->validationNewsletter($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation

        if ($userModel->create($data)) {
            $respon['status'] = 1;
            $respon['message'] = 'Bạn sẽ nhận được tin tức mới nhất qua Email';
        } else {
            $respon['message'] = 'Không thể Thêm';
        }
        return $respon;
    }

    public function login($request)
    {

        $respon['status'] = 0;
        if (empty($request['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($request['data'], true);
        $respon['popupHlv'] = false;
        $userModel = new \Backend\Models\UserModel();
        //Validation
        $validation = $userModel->validationLogin($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation
        $email = FALSE;
        if (filter_var($data['us_name'], FILTER_VALIDATE_EMAIL)) {
            $email = TRUE;
        }

        if ($email) {
            $user = $userModel::findFirst(array(
                'us_email = :userEmail:  
                    AND us_password = :userPassword:',
                'bind' => array(
                    'userEmail' => $data['us_name'],
                    'userPassword' => md5($data['us_password']),
                )));
        } else {
            $user = $userModel::findFirst(array(
                'us_name = :userEmail:  
                    AND us_password = :userPassword:',
                'bind' => array(
                    'userEmail' => $data['us_name'],
                    'userPassword' => md5($data['us_password']),
                )));
        }
        if (empty($user)) {
            $respon['message'] = "Mật khẩu hoặc Tài khoàn không chính xác ";
        } else {
            if(!empty($data['popupHlv'])){
                $respon['popupHlv'] = $data['popupHlv'];
            }
            $user->update_at = date("Y-m-d H:i:s");
            $user->save();
            $this->createSession($user);
            $respon['status'] = 1;
            $respon['message'] = "Đăng nhập thành công";
        }
        return $respon;
    }

    public function loginFacebookAction()
    {
        $response = array("status" => 0, "message" => "Thao tác không thành công");
        if ($this->request->isPost()) {
            $acesstoken = $this->request->getPost("accesstoken", null, false);
            $hlvID = $this->request->getPost("hlvID");

            $fb = new \Facebook\Facebook([
                'app_id' => $this->module_config_frontend->FACEBOOK_ID,
                'app_secret' => $this->module_config_frontend->FACEBOOK_SECRECT,
                'default_graph_version' => 'v2.6',
            ]);

            try {
                // Get the Facebook\GraphNodes\GraphUser object for the current user.
                // If you provided a 'default_access_token', the '{access-token}' is optional.
                $response = $fb->get('/me?fields=id,name,email', $acesstoken);
            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            $user_profile = $response->getGraphUser();
            if (!empty($user_profile)) {
                $email = $user_profile->getEmail();
                $id = $user_profile->getId();
                $username = explode("@", $email);
                $username = $username[0];
                $data_user = array(
                    "email" => $email,
                    "nickname" => $user_profile->getName(),
                    "username" => $username,
                    "id" => $id,
                    "from" => "facebook"
                );
                $response = $this->doSocialLogin($data_user);
            }
        }
        $response['popupHlv'] = $hlvID;
        echo json_encode($response);
        die;
    }

    public function googleCallbackAction()
    {
        $response = array("status" => 0, "message" => "Thao tác không thành công");
        $code = $this->request->getPost("code", null, false);
        if ($code) {
            $google = new \Google_Client();
            $google->setApplicationName($this->module_config_frontend->GOOGLE_NAME);
            $google->setClientId($this->module_config_frontend->GOOGLE_ID);
            $google->setClientSecret($this->module_config_frontend->GOOGLE_API_KEY);
            $google->setRedirectUri('postmessage');
            $scopes = array(
                "https://www.googleapis.com/auth/userinfo.profile",
                "https://www.googleapis.com/auth/userinfo.email"
            );
            $google->setScopes($scopes);
            $google->authenticate($code);
            $request = new \Google_Http_Request("https://www.googleapis.com/oauth2/v2/userinfo?alt=json");
            $userinfo = $google->getAuth()->authenticatedRequest($request);
            $response = $userinfo->getResponseBody();
            $userinfo = json_decode($response, true);
            $id = $userinfo["id"];
            $username = explode("@", $userinfo["email"]);
            $username = $username[0];
            $data_user = array(
                "email" => $userinfo["email"],
                "nickname" => $userinfo["name"],
                "username" => $username,
                "id" => $id,
                "from" => "google"
            );
            $response = $this->doSocialLogin($data_user);
        }
        echo json_encode($response);
        exit();
    }

    private function doSocialLogin($user_data)
    {
        $response = array("status" => 0, "message" => "Thao tác không thành công");
        $userModel = new UserModel();
        $user = $userModel::findFirst(array("us_email = '{$user_data["email"]}'"));
        if (!($user)) {
            //Register
            $data = array(
                'us_name' => $user_data["username"],
                'us_password' => Null,
                'us_full_name' => $user_data["nickname"],
                'us_email' => $user_data["email"],
                'us_social' => $user_data["from"],
                'us_social_id' => $user_data["id"],
                'us_newsletter' => 1,
            );
            if ($userModel->create($data)) {
                $response["status"] = 1;
                $response['message'] = "Đăng nhập thành công";
                $this->createSession($userModel);
            } else {
                $response["message"] = "Đăng kí không thành công";
            }
        } else {
            //Login
            $user->update_at = date("Y-m-d H:i:s");
            $user->save();
            $this->createSession($user);
            $response["status"] = 1;
            $response['message'] = "Đăng nhập thành công";
        }
        return $response;
    }

    public function register($request)
    {
        $respon['status'] = 0;
        if (empty($request['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($request['data'], true);
        $respon['popupHlv'] = false;
        $userModel = new UserModel();
        //Validation
        $validation = $userModel->validationRegister($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation
        if ($userModel->create($data)) {
            if(!empty($data['popupHlv'])){
                $respon['popupHlv'] = $data['popupHlv'];
            }
            $respon['status'] = 1;
            $respon['message'] = 'Chúc Mừng Bạn đã đăng ký thành công';
            $this->createSession($userModel);
        } else {
            $respon['message'] = 'Không thể Thêm';
        }
        return $respon;
    }


    public function logoutAction()
    {
        $this->destroySession();
        return $this->response->redirect('');
    }


    public function forgotPassword($request)
    {
        $name_company = 'VATC';
        $respon['status'] = 0;
        if (empty($request['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($request['data'], true);
        $email = $data['us_email'];
        $userModel = new UserModel();
        $user = $userModel::findFirst(array("us_email = '{$email}'"));
        if (!$user) {
            $respon['message'] = 'Email không tồn tại';
            return $respon;
        }
        $newPass = rand(100000, 999999);
        $respon = array("status" => 0, "message" => "Gửi mail không thành công. Vui lòng liên hệ ".$name_company." để được hỗ trợ !!!");
        $body = "Chào " . $user->us_name . ",<br><br>";
        $body .= "Đây là mật khẩu mới của bạn.<br>";
        $body .= "Mật Khẩu : $newPass <br><br>";
        $body .= "- Lưu ý: Sau khi đăng nhập bạn nên đổi lại mật khẩu mới. <br><br><br>";
        $body .= "Thân,<br>";
        $body .= $name_company;
        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->IsHTML(true);
        $mail->CharSet = "UTF-8";
        $mail->Host = "smtp.zoho.com";
        $mail->SMTPAuth = true;
        $mail->Username = "no-reply@hoidapthutuchaiquan.vn";
        $mail->Password = "Efp8+yY4(&H4+ubb";
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;
        $mail->setFrom('no-reply@hoidapthutuchaiquan.vn', $name_company);
        $mail->addAddress($email, $user->us_full_name);
        $mail->Subject = "Khôi Phục Mật Khẩu";
        $mail->Body = $body;
        if (!$mail->send()) {
            $respon['message'] = 'Không reset được mật khẩu .Xin vui lòng thử lại sau.';
        } else {
            $user->us_password = md5($newPass);
            $user->update();
            $respon['status'] = 1;
            $respon['message'] = '1 Email đã được gửi đến địa chị mail của bạn.Vui lòng check mail để nhận mật khẩu mới';
        }
        return $respon;

    }


    public function userInfo($request)
    {
        if (!empty($this->user)) {
            $respon['status'] = 0;
            if (empty($request['data'])) {
                $respon['message'] = 'Không có dữ liệu';
                return $respon;
            }

            $data = json_decode($request['data'], true);
            $userModel = new UserModel();
            $user = $userModel::findFirst($this->user->us_id);

            if (isset($data['us_email']) && $user->us_email != $data['us_email']) {
                //Validation
                $validation = $userModel::findFirst("us_email = '{$data['us_email']}'");
                if ($validation) {
                    $respon['message'] = "Email đã tồn tại";
                    return $respon;
                }
                //End Validation
            }
            if (isset($data['current_password']) || isset($data['us_password']) || isset($data['us_confirm_password'])) {

                //Validation
                $validation = $user->validationPassword($data);
                if ($validation) {
                    $respon['message'] = $validation;
                    return $respon;
                }
                $data['us_password'] = md5($data['us_password']);
                //End Validation
            }
            if ($user->update($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Cập nhật thông tin thành công';
                $this->createSession($user);
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
            return $respon;
        }
    }

    public function provinceZone($request)
    {
        $districtModel = new \Backend\Models\ZoneDistrictModel();
        $district = $districtModel::find(array("zp_id = '{$request['data']}'", "order" => "zd_name asc"));
        $respon['status'] = 1;
        $respon['data'] = $district->toArray();
        return $respon;
    }

    public function forgotPasswordAction()
    {
        $this->assets->addCss('public/FrontendCore/css/forgot-pass.css', true);
        $this->view->header_title = "Forgot Password User";
    }

    protected function setScript()
    {
        $this->assets->addCss('public/FrontendCore/css/login.css', true);
    }
}
