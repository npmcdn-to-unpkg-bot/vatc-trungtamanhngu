<?php

namespace Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class OrtherPageModel extends ModelBase {

    protected $prefix = "p_";

    public function getSource() {
        return "hq_orther_page";
    }

    public function initialize() {
        parent::initialize();
    }

    public function validationRequest($request) {
        $validation = new Validation();

        $validation->add($this->prefix . 'name', new PresenceOf(
                array(
            'message' => 'Title is required'
                )
        ));
        //check when update
        if (empty($request[$this->prefix . 'id'])) {
            $validation->add($this->prefix . 'name', new UniquenessValidator(array(
                'model' => '\Backend\Models\OrtherPageModel',
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


    public function showCreateDate() {
        return date("F d,Y", strtotime($this->created_at));
    }

    public function showSeoLink() {
        return "page/" . $this->n_seo_link;
    }

}
