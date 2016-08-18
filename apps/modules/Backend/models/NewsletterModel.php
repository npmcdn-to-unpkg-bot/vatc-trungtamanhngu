<?php

namespace Backend\Models;

class NewsletterModel extends ModelBase {

    public function getSource() {
        return "hq_newsletter";
    }

    public function initialize() {
        parent::initialize();
        $this->belongsTo("usa_id", "\Backend\Models\UserAdminModel", "usa_id", array('alias' => 'UserAdminModel'));
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "ne_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
