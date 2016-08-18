<?php

namespace Backend\Models;

use Phalcon\Mvc\Model\Validator\ExclusionIn as ExclusionInValidator;
use Phalcon\Mvc\Model\Validator\InclusionIn as InclusionInValidator;
use Phalcon\Mvc\Model\Message;

class CollectionModel extends ModelBase
{

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("col_id", "\Backend\Models\CategoryCollectionModel", "col_id", array('alias' => 'CategoryCollectionModel'));
        $this->belongsTo("pr_id", "\Backend\Models\ProductModel", "pr_id", array('alias' => 'ProductModel'));
    }

    public function validation()
    {
        if ($this->col_id == "-1") {
            $message = new Message(
                "Chưa chọn Category Collection",
                "type",
                "MyType"
            );

            $this->appendMessage($message);

            return false;
        }
        if ($this->pr_id == "-1") {
            $message = new Message(
                "Chưa chọn Product",
                "type",
                "MyType"
            );

            $this->appendMessage($message);

            return false;
        }

        if ($this->validationHasFailed() == true) {
            return false;
        } else {
            return true;
        }

    }

}
