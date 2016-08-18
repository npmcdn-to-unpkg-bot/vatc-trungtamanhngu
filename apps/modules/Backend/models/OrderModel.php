<?php

namespace Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\ExclusionIn;

class OrderModel extends ModelBase
{

    public function getSource()
    {
        return "hq_order";
    }

    const Fee_tranfer = 55000;

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("zp_id", "\Backend\Models\ZoneProvinceModel", "zp_id", array('alias' => 'ZoneProvinceModel'));
        $this->belongsTo("zd_id", "\Backend\Models\ZoneDistrictModel", "zd_id", array('alias' => 'ZoneDistrictModel'));
        $this->belongsTo("zw_id", "\Backend\Models\ZoneWardModel", "zw_id", array('alias' => 'ZoneWardModel'));
        $this->belongsTo("us_id", "\Backend\Models\UserModel", "us_id", array('alias' => 'UserModel'));
        $this->belongsTo("os_id", "\Backend\Models\OrderStatusModel", "os_id", array('alias' => 'OrderStatusModel'));
        $this->hasMany("or_id", "\Backend\Models\OrderDetailModel", "or_id", array('alias' => 'OrderDetailModel'));
    }

    public function validationRequest($request)
    {

        $validation = new Validation();

        $validation->add('or_name', new PresenceOf(
            array(
                'message' => 'Tên không được để trống'
            )
        ));
        $validation
            ->add('or_email', new PresenceOf(array(
                'message' => 'Email không được để trống'
            )))
            ->add('or_email', new Email(array(
                'message' => 'Không đúng định dạng Email'
            )));
        $validation
            ->add('or_phone', new PresenceOf(
                array(
                    'message' => 'Điện Thoại không được để trống'
                )
            ))
            ->add('or_phone', new StringLength(array(
                'max' => 11,
                'min' => 10,
                'message' => 'Vui lòng nhập đúng số điện thoại của bạn',
            )));
        $validation->add('or_address', new PresenceOf(
            array(
                'message' => 'Địa Chỉ không được để trống'
            )
        ));
        $validation->add('zp_id', new ExclusionIn(array(
            'message' => 'Vui lòng chọn Tỉnh/Thành Phố',
            'domain' => array('0', '-1')
        )));
        $validation->add('zd_id', new ExclusionIn(array(
            'message' => 'Vui lòng chọn Quận/Huyện',
            'domain' => array('0', '-1')
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

    public function showPrice()
    {
        return number_format($this->or_total) . " Vnđ";
    }

    public function showCreateDate()
    {
        return date("d/m/Y", strtotime($this->or_create_date));
    }

    public function setPrice($price)
    {
        return number_format($price) . " Vnđ";
    }
}
