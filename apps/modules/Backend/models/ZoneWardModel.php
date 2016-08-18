<?php

namespace Backend\Models;

class ZoneWardModel extends ModelBase {

    public function initialize() {
        parent::initialize();
        $this->belongsTo("zd_id", "\Backend\Models\ZoneDistrictModel", "cte_id", array('zd_id' => 'ZoneDistrictModel'));
    }

    public function getSource() {
        return "hq_zone_ward";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "zw_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
