<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\StringLength;
use Phalcon\Mvc\Model\Validator\Numericality;

class CategoryCollectionModel extends ModelBase
{

    public function initialize()
    {
        parent::initialize();
        $this->hasMany("col_id", "\Backend\Models\CollectionModel", "col_id", array('alias' => 'CollectionModel'));
    }

    public function getSource()
    {
        return "hq_collection_category";
    }

    public function validation()
    {
        $this
            ->validate(new PresenceOf(array(
                "field" => "col_name",
                "message" => "Name is required"
            )))
            ->validate(new Uniqueness(array(
                "field" => "col_name",
                "message" => "Name is exist"
            )));
        if ($this->validationHasFailed() == true) {
            return false;
        } else {
            return true;
        }

    }

    public function beforeCreate()
    {
        if ($this->get_seo($this->col_name) != $this->col_seo_link) {
            $this->col_seo_link = $this->get_seo($this->col_name);
        }
    }

    public function beforeUpdate()
    {

        if ($this->get_seo($this->col_name) != $this->col_seo_link) {
            $this->col_seo_link = $this->get_seo($this->col_name);
        }
    }

    public function beforeDelete()
    {
        if (count($this->CollectionModel) > 0) {
            $this->CollectionModel->delete();
        }
    }
}
