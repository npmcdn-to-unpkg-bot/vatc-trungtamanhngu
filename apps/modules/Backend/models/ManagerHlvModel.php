<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;

class ManagerHlvModel extends ModelBase
{

    public function getSource()
    {
        return "hq_manager_hlv";
    }

    public function initialize()
    {
        parent::initialize();
        $this->hasMany("hlv_id", "\Backend\Models\ManagerPostsModel", "hlv_id", array('alias' => 'ManagerPostsModel'));
    }

    public function validation()
    {

        $this->validate(
            new PresenceOf(
                array(
                    "field" => "hlv_name",
                    "message" => "Name is required"
                )
            )
        );
        $this->validate(
            new PresenceOf(
                array(
                    "field" => "hlv_description",
                    "message" => "Description is required"
                )
            )
        );

        return $this->validationHasFailed() != true;
    }
    public function showGender()
    {
        if($this->hlv_gender==1){
            return 'Male';
        }else{
            return 'Female';
        }
    }

}
