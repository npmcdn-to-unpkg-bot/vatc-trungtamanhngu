<?php

namespace Backend\Models;

class NewsCategoryModel extends ModelBase {

    public function initialize() {
        parent::initialize();
    }

    public function getSource() {
        return "hq_news_category";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "nc_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
