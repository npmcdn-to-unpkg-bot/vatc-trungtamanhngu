<?php
namespace Backend\Models;
class OrderHistoryModel extends ModelBase {
    
    public function getSource(){
        return "hq_order_history";
    }
   
    public function initialize() {
        parent::initialize();
        $this->belongsTo("usa_id", "\Backend\Models\UserAdminModel", "usa_id", array('alias' => 'UserAdminModel'));
        $this->belongsTo("os_id", "\Backend\Models\OrderStatusModel", "os_id", array('alias' => 'OrderStatusModel'));
        
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "oh_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

