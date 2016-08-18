<?php

namespace HqLibrary\Payment;

abstract class Payment {

    protected $_id;
    public $_promotion = array(
        "mobi"    => 0,
        "vina"    => 0,
        "viettel" => 0
    );
    public $pin;
    public $seri;
    public $card_type;
    public $msg;
    public $user;
    public $phone;
    public $transaction;
    public $channel_id;
    public $channel_name;

    public function __construct($data_card, $data_user, $channel = 0, $channel_name = "direct")
    {
        $this->user         = $data_user;
        $this->pin          = $data_card["card_number"];
        $this->seri         = $data_card["card_seri"];
        $this->phone        = $data_card["card_phone"];
        $this->card_type    = $data_card["card_type"];
        $this->transaction  = $this->user["mem_id"] . "_" . time();
        $this->channel_id   = $channel;
        $this->channel_name = $channel_name;
    }

    protected function _preCharge($payment_obj)
    {
        $payment_obj->pa_channel_id          = $this->channel_id;
        $payment_obj->pa_channel_name        = $this->channel_name;
        $payment_obj->pa_card_number         = $this->pin;
        $payment_obj->pa_card_serial         = $this->seri;
        $payment_obj->pa_created_date        = date("Y-m-d H:i:s");
        $payment_obj->pa_message             = "Chờ Server Đối Tác Xử Lý";
        $payment_obj->pa_pid                 = $this->user["mem_link_id"];
        $payment_obj->pa_method              = $this->_id;
        $payment_obj->pa_phone               = $this->phone;
        $payment_obj->pa_partner_transaction = $this->transaction;
        $payment_obj->pa_status              = 2;
        $payment_obj->pa_transaction         = $this->transaction;
        $payment_obj->pa_type                = $this->card_type;
        $payment_obj->pa_user_public_id      = $this->user["mem_public_id"];
        $payment_obj->pa_value               = 0;
        $payment_obj->save();
        return $payment_obj;
    }

    public function charge($payment_model)
    {
        $payment_obj = $this->_preCharge($payment_model);
        $response    = $this->_doCharge();
        $this->_endCharge($payment_obj, $response);
        return $response;
    }

    protected function _doCharge()
    {
        $response = array("status" => 0, "message" => "", "value" => 0, "partner_transaction" => "");
        return $response;
    }

    protected function _endCharge($payment_obj, $response)
    {
        $payment_obj->pa_partner_transaction = $response["partner_transaction"];
        $payment_obj->pa_message             = $response["message"];
        $payment_obj->pa_status              = $response["status"];
        $payment_obj->pa_value               = $response["value"];
        $payment_obj->save();
    }

    public function checkCard($serial)
    {
        return array();
    }

}
