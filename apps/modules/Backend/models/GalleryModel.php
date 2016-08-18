<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;

class GalleryModel extends ModelBase
{

    public function getSource()
    {
        return "hq_gallery";
    }

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("hlv_id", "\Backend\Models\ManagerHlvModel", "hlv_id", array('alias' => 'ManagerHlvModel'));
    }



}
