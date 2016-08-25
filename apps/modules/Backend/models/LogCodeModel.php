<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;

class LogCodeModel extends ModelBase
{

    public function getSource()
    {
        return "hq_log_code";
    }

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("us_id", "\Backend\Models\UserModel", "us_id", array('alias' => 'UserModel'));
    }



}
