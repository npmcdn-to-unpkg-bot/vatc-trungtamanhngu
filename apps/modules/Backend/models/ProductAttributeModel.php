<?php
namespace Backend\Models;
class ProductAttributeModel extends ModelBase {
    
    public function getSource(){
        return "hq_product_attribute";
    }
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("pr_id", "\Backend\Models\ProductModel", "pr_id",array('alias' => 'ProductModel'));
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "hpa_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

