<?php
namespace Backend\Models;
class ProductAvatarModel extends ModelBase {
    
    public function getSource(){
        return "hq_product_avatar";
    }
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("pr_id", "\Backend\Models\ProductModel", "pr_id",array('alias' => 'ProductModel'));
        $this->belongsTo("la_id", "\Backend\Models\TypeGaleryModel", "la_id",array('alias' => 'TypeGaleryModel'));
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "pa_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

