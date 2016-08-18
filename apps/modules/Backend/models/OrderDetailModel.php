<?php

namespace Backend\Models;

class OrderDetailModel extends ModelBase {

    public function getSource() {
        return "hq_order_detail";
    }


    public function initialize() {
        parent::initialize();
        $this->belongsTo("or_id", "\Backend\Models\OrderModel", "or_id", array('alias' => 'OrderModel'));
        $this->belongsTo("pr_id", "\Backend\Models\ProductModel", "pr_id", array('alias' => 'ProductModel'));
        $this->belongsTo("od_size", "\Backend\Models\ProductListOptionModel", "plo_id", array('alias' => 'SizeModel'));
        $this->belongsTo("od_color", "\Backend\Models\ProductListOptionModel", "plo_id", array('alias' => 'ColorModel'));
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "od_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
