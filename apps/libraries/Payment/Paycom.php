<?php

namespace payment;

class paycom extends \payment\payment
{

    protected $_id       = 1;
    private $uid         = 6;
    private $user_api    = "5493ca3feac52";
    private $pass_api    = "a696ab1af7640df69859daebb8df1871";
    private $_array_card = array(
        "vina"    => "3",
        "mobi"    => "2",
        "viettel" => "1"
    );
    public $_promotion   = array(
        "mobi"    => 0,
        "vina"    => 0,
        "viettel" => 0
    );
    public $info_card;
    public $note;

    protected function _doCharge()
    {
        $fields = array(
            'merchant_id' => $this->uid,
            'pin'         => $this->pin,
            'seri'        => $this->seri,
            'card_type'   => $this->_array_card[$this->card_type],
            'note'        => $this->note
        );

        $ch              = curl_init("http://paycom.vn/api/card");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_USERPWD, $this->user_api . ":" . $this->pass_api);
        $result          = curl_exec($ch);
        $result          = json_decode($result);
        curl_close($ch);
        $this->msg       = $result->msg;
        $this->info_card = $result->info_card;
        if (intval($this->info_card) >= 10000)
        {
            $response = array(
                "status"              => 1,
                "message"             => "Bạn đã nạp : " . $this->info_card . " VNĐ. Vào tài khoản : " . $this->user["ch_nickname"],
                "partner_transaction" => $this->transaction,
                "value"               => $this->info_card,
                "promotion"           => $this->_promotion[$this->card_type]
            );
        }
        else
        {
            $response = array(
                "status"              => 0,
                "message"             => $this->msg,
                "partner_transaction" => $this->transaction,
                "value"               => 0,
                "promotion"           => 0
            );
        }
        return $response;
    }

}
