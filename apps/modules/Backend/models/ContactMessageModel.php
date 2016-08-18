<?php

namespace Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Confirmation;

class ContactMessageModel extends ModelBase
{

    public function getSource()
    {
        return "hq_contact_message";
    }

    public function initialize()
    {
        parent::initialize();
    }
    public function validationMessage($request) {
        $validation = new Validation();

        $validation
            ->add('cm_name', new PresenceOf(array(
                'message' => 'Name is required'
            )))
            ->add('cm_name', new StringLength(array(
                'min' => 6,
                'message' => 'Name tối thiểu 6 kí tự',
            )));
        ;
        $validation
            ->add('cm_email', new PresenceOf(array(
                'message' => 'Email is required'
            )))
            ->add('cm_email', new Email(array(
                'message' => 'Email is not valid'
            )));

        $validation
            ->add('cm_phone', new PresenceOf(
                array(
                    'message' => 'Phone is required'
                )
            ))
            ->add('cm_phone', new StringLength(array(
                'max' => 11,
                'min' => 10,
                'message' => 'Vui lòng nhập đúng số điện thoại của bạn',
            )));
        $validation
            ->add('cm_message', new PresenceOf(
                array(
                    'message' => 'Message is required'
                )
            ))
            ->add('cm_message', new StringLength(array(
                'min' => 60,
                'message' => 'Message quá ngắn',
            )));

        $messages = $validation->validate($request);
        if (count($messages)) {
            $text = '';
            foreach ($messages as $message) {
                $text.= $message . "<br>";
            }
            return $text;
        }
        return FALSE;
    }
}
