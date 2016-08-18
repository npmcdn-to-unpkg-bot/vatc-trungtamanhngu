<?php

namespace Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class MenuModel extends ModelBase {

    protected $prefix = "mn_";

    public function initialize() {
        parent::initialize();
    }

    public function getSource() {
        return "hq_menu";
    }


    public function validationRequest($request) {
        $validation = new Validation();

        $validation->add($this->prefix.'name', new PresenceOf(
                array(
            'message' => 'Name is required'
                )
        ));
        $validation->add($this->prefix.'link', new PresenceOf(
                array(
            'message' => 'Link is required'
                )
        ));
        //check when update
        if (empty($request[$this->prefix.'id'])) {
            $validation->add($this->prefix.'name',new UniquenessValidator(array(
                'model' => '\Backend\Models\MenuModel',
                'message' => 'Title đã tồn tại'
            )));
        }
        
        $messages = $validation->validate($request);
        if (count($messages)) {
            $text = '';
            foreach ($messages as $message) {
                $text.= $message . "\n";
            }
            return $text;
        }
        return FALSE;
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "mn_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
