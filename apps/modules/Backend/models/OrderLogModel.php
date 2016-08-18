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

class OrderLogModel extends ModelBase
{

    public function getSource()
    {
        return "hq_order_log";
    }

    public function initialize()
    {
        parent::initialize();
    }

}
