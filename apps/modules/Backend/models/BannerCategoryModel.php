<?php

namespace Backend\Models;

class BannerCategoryModel extends ModelBase {

    public function initialize() {
        parent::initialize();
    }

    public function getSource() {
        return "hq_banner_category";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "bc_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
