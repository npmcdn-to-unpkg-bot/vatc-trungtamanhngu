<?php

namespace Backend\Models;

class ZoneProvinceModel extends ModelBase {

    public function initialize() {
        parent::initialize();
        $this->hasMany("zp_id", "\Backend\Models\AgencyModel", "zp_id", array('alias' => 'AgencyModel'));
    }

    public function getSource() {
        return "hq_zone_province";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "zp_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
