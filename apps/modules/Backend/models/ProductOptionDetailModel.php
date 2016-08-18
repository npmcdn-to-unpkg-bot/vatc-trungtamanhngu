<?php
namespace Backend\Models;
class ProductOptionDetailModel extends ModelBase {
    
    public function getSource(){
        return "hq_product_option_detail";
    }
      
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("pr_id", "\Backend\Models\ProductModel", "pr_id",array('alias' => 'ProductModel'));
        $this->belongsTo("plo_id", "\Backend\Models\ProductListOptionModel", "plo_id",array('alias' => 'ProductListOptionModel'));
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "pod_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

