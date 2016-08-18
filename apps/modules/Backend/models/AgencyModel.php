<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;
use Phalcon\Mvc\Model\Validator\InclusionIn as InclusionInValidator;
use Phalcon\Mvc\Model\Validator\ExclusionIn as ExclusionInValidator;

class AgencyModel extends ModelBase
{

    public function getSource()
    {
        return "hq_agency";
    }

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("zp_id", "\Backend\Models\ZoneProvinceModel", "zp_id", array('alias' => 'ZoneProvinceModel'));
    }

    public function updateByID($data, $id)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "ag_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

    public function validation()
    {
        $this
            ->validate(new PresenceOf(array(
                "field" => "ag_name",
                "message" => "Name is required"
            )))
            ->validate(new Uniqueness(array(
                "field" => "ag_name",
                "message" => "Name is exist"
            )));
        $this
            ->validate(new PresenceOf(array(
                "field" => "ag_name",
                "message" => "Code is required"
            )));
        $this->validate(new ExclusionInValidator(array(
            "field" => 'zp_id',
            'domain' => array('-1'),
            "message" => "Bạn chưa chọn Tỉnh/Thành Phố"
        )));
        if ($this->validationHasFailed() == true) {
            return false;
        } else {
            return true;
        }

    }
}
