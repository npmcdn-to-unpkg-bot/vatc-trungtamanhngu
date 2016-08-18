<?php

namespace Backend\Models;

class ProductListOptionModel extends ModelBase
{

    public static $Color = 1;
    public static $Size = 2;

    public function getSource()
    {
        return "hq_product_list_option";
    }

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("po_id", "\Backend\Models\ProductOptionModel", "po_id", array('alias' => 'ProductOptionModel'));
        $this->hasMany("plo_id", "\Backend\Models\ProductImageModel", "plo_id", array('alias' => 'ProductImageModel'));
        $this->hasMany("plo_id", "\Backend\Models\ProductOptionDetailModel", "plo_id", array('alias' => 'ProductOptionDetailModel'));
    }

    public function updateByID($data, $id)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "plo_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

    public static function getOptionById($id)
    {
        $option = self::findFirst($id);
        return $option;

    }
}
