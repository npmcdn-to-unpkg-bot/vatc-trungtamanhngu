<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;

class CouponModel extends ModelBase
{

    public function initialize()
    {
        parent::initialize();

    }

    public function getSource()
    {
        return "hq_coupon";
    }


    public function validation()
    {
        $this
            ->validate(new PresenceOf(array(
                "field" => "co_name",
                "message" => "Name is required"
            )))
            ->validate(new Uniqueness(array(
                "field" => "co_name",
                "message" => "Name is exist"
            )));
        $this
            ->validate(new PresenceOf(array(
                "field" => "co_code",
                "message" => "Code is required"
            )))
            ->validate(new Uniqueness(array(
                "field" => "co_code",
                "message" => "Code is exist"
            )))
            ->validate(new StringLength(array(
                "field" => "co_code",
                "min" => 6,
                "message" => "Code must have 6 character"
            )));
        $this
            ->validate(new PresenceOf(array(
                "field" => "co_discount",
                "message" => "Discount is required"
            )))
            ->validate(new Numericality(array(
                "field" => "co_discount",
                "message" => "Discount must be numeric"
            )));
        $this
            ->validate(new PresenceOf(array(
                "field" => "co_total",
                "message" => "Total is required"
            )))
            ->validate(new Numericality(array(
                "field" => "co_total",
                "message" => "Total must be numeric"
            )));
        $this
            ->validate(new PresenceOf(array(
                "field" => "co_number",
                "message" => "Number is required"
            )))
            ->validate(new Numericality(array(
                "field" => "co_number",
                "message" => "Number must be numeric"
            )));
        $this
            ->validate(new PresenceOf(array(
                "field" => "co_uses_total",
                "message" => "User Number is required"
            )))
            ->validate(new Numericality(array(
                "field" => "co_uses_total",
                "message" => "User Number must be numeric"
            )));
        if ($this->validationHasFailed() == true) {
            return false;
        } else {
            return true;
        }

    }


    public function showType()
    {
        if ($this->co_type == 1) {
            return 'Percentage';
        } else {
            return 'Fixed Amount';
        }
    }

    public function showDate($date)
    {
        return date("d/m/Y H:i:s", strtotime($date));
    }

    public function showDiscount()
    {
        if ($this->co_type == 1) {
            return $this->co_discount . "%";
        } else {
            return number_format($this->co_discount) . " Vnđ";
        }
    }

    public function showTotalDiscount()
    {
        return number_format($this->co_total) . " Vnđ";
    }
}
