<?php

namespace Backend\Models;

class BannerModel extends ModelBase {

    public static $SliderHome = 2;
    public static $SliderCollection = 3;
    public static $FeatureNMPage = 4;
    public static $SliderNinomaxxPage = 5;

    public function getSource() {
        return "hq_banner";
    }

    public function initialize() {
        parent::initialize();
        $this->belongsTo("bc_id", "\Backend\Models\BannerCategoryModel", "bc_id", array('alias' => 'BannerCategoryModel'));
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "ba_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
