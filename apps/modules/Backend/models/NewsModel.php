<?php

namespace Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class NewsModel extends ModelBase {

    protected $prefix = "n_";

    public function getSource() {
        return "hq_news";
    }

    public function initialize() {
        parent::initialize();
        $this->belongsTo("nc_id", "\Backend\Models\NewsCategoryModel", "nc_id", array('alias' => 'NewsCategoryModel'));
    }

    public function validationRequest($request) {
        $validation = new Validation();

        $validation->add($this->prefix . 'name', new PresenceOf(
                array(
            'message' => 'Name is required'
                )
        ));
        //check when update
        if (empty($request[$this->prefix . 'id'])) {
            $validation->add($this->prefix . 'name', new UniquenessValidator(array(
                'model' => '\Backend\Models\NewsModel',
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
        $where = "n_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

    public function showCreateDate() {
        return date("d-m-Y", strtotime($this->created_at));
    }

    public function showSeoLink() {
        return "news/detail/" . $this->n_seo_link;
    }

    public function showKeywords() {
        $html = '';
        if (!empty($this->n_keywords)) {
            $keywords = explode(",", $this->n_keywords);
            foreach ($keywords as $val) {
                $html.='<a href="javascript:;" >' . $val . ' ,</a>';
            }
        }
        return $html;
    }

}
