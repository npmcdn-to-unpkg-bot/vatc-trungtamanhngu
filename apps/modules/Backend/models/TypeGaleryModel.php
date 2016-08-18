<?php

namespace Backend\Models;

class TypeGaleryModel extends ModelBase {

    public function initialize() {
        parent::initialize();
    }

    public function getSource() {
        return "hq_list_type_image";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "la_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
