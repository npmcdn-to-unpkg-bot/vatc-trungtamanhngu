<?php
namespace Backend\Models;
class AdminRoleModel extends ModelBase {
    public function getSource(){
        return "hq_admin_role";
    }
    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "role_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }
}

