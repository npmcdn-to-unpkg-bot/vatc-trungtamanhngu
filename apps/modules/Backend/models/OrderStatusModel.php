<?php

namespace Backend\Models;

class OrderStatusModel extends ModelBase {

    public function initialize() {
        parent::initialize();
    }

    public function getSource() {
        return "hq_order_status";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "os_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
