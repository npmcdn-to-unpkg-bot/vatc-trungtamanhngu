<?php
namespace Backend\Models;
class ProductOptionModel extends ModelBase {
    
    public function getSource(){
        return "hq_product_option";
    }
   
    public function initialize()
    {
        parent::initialize();
        $this->hasMany("po_id", "\Backend\Models\ProductListOptionModel", "po_id",array('alias' => 'ProductListOptionModel'));
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "po_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

