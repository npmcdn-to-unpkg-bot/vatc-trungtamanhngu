<?php

namespace payment;

class vielink extends \payment\payment {

    protected $_id       = 3;
    private $uid         = 6;
    private $user_api    = "sgg";
    private $pass_api    = "sgg@@!!!889";
    private $_array_card = array(
        "vina"    => "VINA",
        "mobi"    => "MOBI",
        "viettel" => "VT"
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
        $data        = array(
            'agentcode'  => trim($this->user_api),
            'catecode'   => $this->_array_card[$this->card_type],
            'cardcode'   => $this->pin,
            'cardserial' => trim($this->seri),
            'tranid'     => $this->transaction,
        );
//        $soap_client = new \SoapClient(null, array('location'           => "http://testpay.pay365.vn/CardCharging.asmx",
//            'uri'                => "localhost",
//            'connection_timeout' => 120,
//            'encoding'           => 'utf-8',
//            'trace'              => 1,
//            'exceptions'         => 0,
//                )
//        );
//        $result      = $soap_client->__soapCall("CardChargingController.UseCard", array(
//            "cardrequest" => json_encode($data)
//        ));
        $soap_client = new \SoapClient("http://charging.pay365.vn/CardCharging.asmx?wsdl", array("trace" => 1, "exception" => 0));
        $result      = $soap_client->UseCard(array(
            "agentCode" => $this->user_api,
            "data"      => $this->Encrypt($this->pass_api, json_encode($data))
        ));
        $result      = json_decode($result->UseCardResult);
        if (!empty($result))
        {
            $this->msg       = $result->msg;
            $this->info_card = $result->amount;
        }
        else
        {
            $this->msg       = "Hệ Thống Đang Bận Vui Lòng thử lại sau vài giây bạn nhé !!!";
            $this->info_card = 0;
        }
        if (!empty($result->data_transId))
        {
            $this->transaction = $result->data_transId;
        }
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

    private function Encrypt($key_seed, $input)
    {
        $input          = trim($input);
        $block          = mcrypt_get_block_size('tripledes', 'ecb');
        $len            = strlen($input);
        $padding        = $block - ($len % $block);
        $input .= str_repeat(chr($padding), $padding);
        // generate a 24 byte key from the md5 of the seed  
        $key            = substr(md5($key_seed), 0, 24);
        $iv_size        = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
        $iv             = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        // encrypt       
        $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, $iv);
        // clean up output and return base64 encoded        
        return base64_encode($encrypted_data);
    }

    public function checkCard($serial)
    {
        
    }

}
