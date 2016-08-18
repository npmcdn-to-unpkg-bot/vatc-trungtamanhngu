<?php

namespace Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Confirmation;

class UserModel extends ModelBase
{

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("zp_id", "\Backend\Models\ZoneProvinceModel", "zp_id", array('alias' => 'ZoneProvinceModel'));
        $this->belongsTo("zd_id", "\Backend\Models\ZoneDistrictModel", "zd_id", array('alias' => 'ZoneDistrictModel'));
        $this->belongsTo("zw_id", "\Backend\Models\ZoneWardModel", "zw_id", array('alias' => 'ZoneWardModel'));
        $this->belongsTo("us_id", "\Backend\Models\OrderModel", "us_id", array('alias' => 'OrderModel'));
    }

    public function getSource()
    {
        return "hq_user";
    }

    public function validationNewsletter($request)
    {
        $validation = new Validation();

        $validation
            ->add('us_email', new PresenceOf(array(
                'message' => 'Email is required'
            )))
            ->add('us_email', new Email(array(
                'message' => 'Email is not valid'
            )));


        $messages = $validation->validate($request);
        if (count($messages)) {
            $text = '';
            foreach ($messages as $message) {
                $text .= $message . "\n";
            }
            return $text;
        }
        return FALSE;
    }

    public function validationRegister($request)
    {

        $validation = new Validation();

        $validation
            ->add('us_name', new PresenceOf(
                array(
                    'message' => 'User Name is required'
                )
            ))
            ->add('us_name', new UniquenessValidator(array(
                'model' => '\Backend\Models\UserModel',
                'message' => 'User Name đã tồn tại'
            )))
            ->add('us_name', new StringLength(array(
                'min' => 6,
                'message' => 'User Name tối thiểu 6 kí tự',
            )))
            ->add('us_name', new RegexValidator(array(
                'pattern' => '/^[a-zA-Z0-9@.]+$/',
                'message' => 'User Name không được chứa kí tự đặc biệt'
            )));
        $validation
            ->add('us_email', new PresenceOf(array(
                'message' => 'Email is required'
            )))
            ->add('us_email', new Email(array(
                'message' => 'Email is not valid'
            )))
            ->add('us_email', new UniquenessValidator(array(
                'model' => '\Backend\Models\UserModel',
                'message' => 'Email đã tồn tại'
            )));

        $validation
            ->add('us_password', new Confirmation(array(
                'message' => 'Mật Khẩu xác nhận không trùng khớp',
                'with' => 'us_confirm_password'
            )))
            ->add('us_password', new StringLength(array(
                'min' => 6,
                'message' => 'Password tối thiểu 6 kí tự',
            )));
        $messages = $validation->validate($request);
        if (count($messages)) {
            $text = '';
            foreach ($messages as $message) {
                $text .= $message . "<br>";
            }
            return $text;
        }
        return FALSE;
    }

    public function validationPassword($request)
    {
        if (md5($request['current_password']) != $this->us_password) {
            return 'Mật khẩu hiện tại không đúng.';
        }
        $validation = new Validation();

        $validation
            ->add('us_password', new Confirmation(array(
                'message' => 'Mật Khẩu xác nhận không trùng khớp',
                'with' => 'us_confirm_password'
            )))
            ->add('us_password', new StringLength(array(
                'min' => 6,
                'message' => 'Password tối thiểu 6 kí tự',
            )));
        if ($request['current_password'] == $request['us_confirm_password']) {
            return 'Mật khẩu mới không được trùng với mật khẩu cũ';
        }
        $messages = $validation->validate($request);
        if (count($messages)) {
            $text = '';
            foreach ($messages as $message) {
                $text .= $message . "<br>";
            }
            return $text;
        }
        return FALSE;
    }

    public function validationLogin($request)
    {
        $validation = new Validation();

        $validation
            ->add('us_name', new PresenceOf(
                array(
                    'message' => 'User Name is required'
                )
            ))
            ->add('us_name', new StringLength(array(
                'min' => 6,
                'message' => 'User Name tối thiểu 6 kí tự',
            )))
            ->add('us_name', new RegexValidator(array(
                'pattern' => '/^[a-zA-Z0-9@.]+$/',
                'message' => 'User Name không được chứa kí tự đặc biệt'
            )));

        $validation
            ->add('us_password', new PresenceOf(
                array(
                    'message' => 'Password is required'
                )
            ))
            ->add('us_password', new StringLength(array(
                'min' => 6,
                'message' => 'Password tối thiểu 6 kí tự',
            )));
        $messages = $validation->validate($request);
        if (count($messages)) {
            $text = '';
            foreach ($messages as $message) {
                $text .= $message . "<br>";
            }
            return $text;
        }
        return FALSE;
    }

    public function updateByID($data, $id)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "us_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

    public function beforeCreate()
    {
//        $this->us_full_name=$this->us_name;
        if (!empty($this->us_password)) {
            $this->us_password = md5($this->us_password);
        }
    }

    public function setDate($date)
    {
        return date("d/m/Y", strtotime($date));
    }
}
