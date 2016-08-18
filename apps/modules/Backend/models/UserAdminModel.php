<?php

namespace Backend\Models;

class UserAdminModel extends ModelBase {

    public function initialize() {
        parent::initialize();
    }

    public function getSource() {
        return "hq_user_admin";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "usa_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
