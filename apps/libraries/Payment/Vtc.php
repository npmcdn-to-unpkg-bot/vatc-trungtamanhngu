<?php

namespace HqLibrary\Payment;

class Vtc extends \HqLibrary\Payment\Payment {

    protected $_id       = 1;
    private $url         = "http://api.vtcebank.vn:8888/VMSCardAPI/card.asmx?wsdl";
    private $uid         = 1;
    private $username    = "665096";
    private $secret      = "665096@BTD";
    private $_array_card = array(
        'vina'          => 'GPC',
        'mobi'          => 'VMS',
        'viettel'       => 'VTEL',
        'sfone'         => 'SFONE',
        'vietnammobile' => 'VNM'
    );
    public $_promotion   = array(
        'vina'          => 0,
        'mobi'          => 0,
        'viettel'       => 0,
        'sfone'         => 0,
        'vietnammobile' => 0
    );
    public $info_card;
    public $note;
    private $_error_card = array(
        "-1"   => "Thẻ đã sử dụng",
        "-2"   => "Thẻ đã bị khóa",
        "-3"   => "Thẻ hết hạn sử dụng",
        "-4"   => "Thẻ chưa kích hoạt",
        "-5"   => "TransID không hợp lệ",
        "-6"   => "Mã thẻ và số Serial không khớp",
        "-8"   => "Cảnh báo số lần giao dịch lỗi của một tài khoản",
        "-9"   => "Thẻ thử quá số lần cho phép",
        "-10"  => "CardID không hợp lệ",
        "-11"  => "CardCode không hợp lệ",
        "-12"  => "Thẻ không tồn tại",
        "-13"  => "Sai cấu trúc Description",
        "-14"  => "Mã dịch vụ không tồn tại",
        "-15"  => "Thiếu thông tin khách hàng",
        "-16"  => "Mã giao dịch không hợp lệ",
        "-90"  => "Sai tên hàm",
        "-98"  => "Giao dịch thất bại do Lỗi hệ thống",
        "-99"  => "Giao dịch thất bại do Lỗi hệ thống",
        "-999" => "Hệ thống Telco tạm ngừng",
        "-100" => "Giao dịch nghi vấn (xác minh kết quả qua kênh đối soát)"
    );

    public function __construct($data_card, $data_user, $channel = 0, $channel_name = "direct")
    {
        $this->user         = $data_user;
        $this->pin          = $data_card["card_number"];
        $this->seri         = $data_card["card_seri"];
        $this->phone        = $data_card["card_phone"];
        $this->card_type    = $data_card["card_type"];
        $this->transaction  = $this->user["mem_id"] . time();
        $this->channel_id   = $channel;
        $this->channel_name = $channel_name;
    }

    protected function _doCharge()
    {

        $arrDescription = array($this->_array_card[$this->card_type], $this->transaction, $this->user["mem_public_id"]);
        $strXml         = '<?xml version="1.0" encoding="utf-16"?>';
        $strXml .= '<CardRequest>';
        $strXml .= '<Function>UseCard</Function>';
        $strXml .= '<CardID>' . $this->seri . '</CardID>';
        $strXml .= '<CardCode>' . $this->pin . '</CardCode>';
        $strXml .= '<Description>' . implode('|', $arrDescription) . '</Description>';
        $strXml .= '</CardRequest>';
        $soap_client    = new \SoapClient($this->url, [
            'trace'     => 1,
            'exception' => 0
        ]);
        $response       = $soap_client->Request(array(
            "PartnerID"   => $this->username,
            "RequestData" => (string) $this->encryptLocalPrivateKey($strXml)
        ));
        $content        = $this->decryptLocalPrivateKey($response->RequestResult);
        $content        = simplexml_load_string(preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $content));
        if (!empty($content))
        {
            $content     = json_encode($content);
            $content     = json_decode($content, true);
            $description = "";
            if (isset($content["Description"]))
            {
                $description = explode("|", $content["Description"]);
            }
            $content["ResponseStatus"] = 10000;
            if ($content["ResponseStatus"] >= 10000)
            {
                $this->info_card = $content["ResponseStatus"];
                $this->msg       = "Success";
            }
            else
            {
                $this->info_card = 0;
                $this->msg       = $this->_error_card[$content["ResponseStatus"]];
            }
        }
        else
        {
            $this->msg       = "Hệ Thống Đang Bận Vui Lòng thử lại sau vài giây bạn nhé !!!";
            $this->info_card = 0;
        }
        if (intval($this->info_card) >= 10000)
        {
            $response = array(
                "status"              => 1,
                "message"             => "Bạn đã nạp : " . $this->info_card . " VNĐ. Vào tài khoản : " . $this->user["mem_nickname"],
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

    function Encrypt($input, $key_seed)
    {
        $input          = trim($input);
        $block          = mcrypt_get_block_size('tripledes', 'ecb');
        $len            = strlen($input);
        $padding        = $block - ($len % $block);
        $input .= str_repeat(chr($padding), $padding);
        $key            = substr(md5($key_seed), 0, 24);
        $iv_size        = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
        $iv             = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, $iv);
        return base64_encode($encrypted_data);
    }

    function Decrypt($input, $key_seed)
    {
        $input   = base64_decode($input);
        $key     = substr(md5($key_seed), 0, 24);
        $text    = mcrypt_decrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, '12345678');
        $block   = mcrypt_get_block_size('tripledes', 'ecb');
        $packing = ord($text{strlen($text) - 1});
        if ($packing and ( $packing < $block))
        {
            for ($P = strlen($text) - 1; $P >= strlen($text) - $packing; $P--)
            {
                if (ord($text{$P}) != $packing)
                {
                    $packing = 0;
                }
            }
        }
        $text = substr($text, 0, strlen($text) - $packing);
        return $text;
    }

    public function encryptLocalPrivateKey($input)
    {

        $input = trim($input);
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $len   = strlen($input);

        $padding = $block - ($len % $block);
        $input .= str_repeat(chr($padding), $padding);

        // generate a 24 byte key from the md5 of the seed 

        $key = substr(md5($this->secret), 0, 24);

        $iv_size = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);

        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        // encrypt  
        $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, $iv);

        // clean up output and return base64 encoded  
        return base64_encode($encrypted_data);
    }

    protected function decryptLocalPrivateKey($input)
    {

        $input = base64_decode($input);


        $key = substr(md5($this->secret), 0, 24);

        $text = mcrypt_decrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, '12345678');

        $block = mcrypt_get_block_size('tripledes', 'ecb');

        $packing = ord($text{strlen($text) - 1});

        if ($packing and ( $packing < $block))
        {

            for ($P = strlen($text) - 1; $P >= strlen($text) - $packing; $P--)
            {

                if (ord($text{$P}) != $packing)
                {

                    $packing = 0;
                }
            }
        }
        $text = substr($text, 0, strlen($text) - $packing);
        return $text;
    }

    public function checkCard($serial)
    {
        
    }

}
