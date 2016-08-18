<?php

namespace Backend\Models;

class CouponLogModel extends ModelBase
{

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("co_code", "\Backend\Models\CouponModel", "co_code", array('alias' => 'CouponModel'));
        $this->belongsTo("us_id", "\Backend\Models\UserModel", "us_id", array('alias' => 'UserModel'));
        $this->belongsTo("or_id", "\Backend\Models\OrderModel", "or_id", array('alias' => 'OrderModel'));
    }

    public function getSource()
    {
        return "hq_coupon_log";
    }

    public function beforeCreate()
    {
        $couponModel = new CouponModel();
        $coupon = $couponModel::findFirst(array("co_code = '{$this->co_code}'"));
        $coupon->co_number = $coupon->co_number - 1;
        $coupon->update();
    }

}
