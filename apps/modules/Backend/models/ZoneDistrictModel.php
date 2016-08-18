<?php

namespace Backend\Models;

class ZoneDistrictModel extends ModelBase {

    public function initialize() {
        parent::initialize();
        $this->belongsTo("zp_id", "\Backend\Models\ZoneProvinceModel", "zp_id", array('alias' => 'ZoneProvinceModel'));
    }

    public function getSource() {
        return "hq_zone_district";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "zd_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
