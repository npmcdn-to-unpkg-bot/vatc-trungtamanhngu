<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;

class ManagerPostsModel extends ModelBase
{

    public function getSource()
    {
        return "hq_manager_posts";
    }

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("hlv_id", "\Backend\Models\ManagerHlvModel", "hlv_id", array('alias' => 'ManagerHlvModel'));
        $this->belongsTo("us_id", "\Backend\Models\UserModel", "us_id", array('alias' => 'UserModel'));
    }

    public function validation()
    {

        $this->validate(new PresenceOf(array("field" => "hlv_id", "message" => "Coach is required")));
        $this->validate(new PresenceOf(array("field" => "us_id", "message" => "User is required")));
        $this->validate(new PresenceOf(array("field" => "p_description", "message" => "Description is required")));

        return $this->validationHasFailed() != true;
    }

    public function showClassHlv()
    {
        if ($this->hlv_id == 1) {
            return " hlv1 ";
        }
        elseif ($this->hlv_id == 2) {
            return " hlv2 ";
        }
        else {
            return " hlv3 ";
        }
    }

    public function showImageIconHlv()
    {
        if ($this->hlv_id == 1) {
            return "/public/FrontendCore/images/icon-hlv1.png";
        }
        elseif ($this->hlv_id == 2) {
            return "/public/FrontendCore/images/icon-hlv2.png";
        }
        else {
            return "/public/FrontendCore/images/icon-hlv3.png";
        }
    }

}
