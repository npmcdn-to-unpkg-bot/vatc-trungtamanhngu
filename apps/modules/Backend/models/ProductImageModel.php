<?php
namespace Backend\Models;
class ProductImageModel extends ModelBase {
    
    public function getSource(){
        return "hq_product_image";
    }
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("pr_id", "\Backend\Models\ProductModel", "pr_id",array('alias' => 'ProductModel'));
        $this->belongsTo("pod_id", "\Backend\Models\ProductOptionDetailModel", "pod_id",array('alias' => 'ProductOptionDetailModel'));
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "pi_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

