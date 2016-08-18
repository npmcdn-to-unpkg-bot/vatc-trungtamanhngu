<?php

namespace payment;

class epay extends \payment\payment
{

    protected $_id                  = 2;
    private $uid                    = 2;
    private $url                    = 'http://charging-service.megapay.net.vn/CardChargingGW_V2.0/services/Services?wsdl';
    private $username               = "HQ070";
    private $password               = "besikvhoq";
    private $merchant_id            = 'HQ070';
    private $merchant_code          = '00837';
    private $mpin                   = 'xtuwplrbg';
    private $local_private_key_path = '';
    private $local_private_key      = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCoab8v0jLpCS8203aW5t2QyGinKyOh6exjZAtpo+JUu4h2LGF5
ek+rlHjzTrG8Obo2bPNcENHuyv7p0e0X6aDe2dQR0SL/x5d8jlxEJyfc37bvkvZk
KyOqNnSlnW48vWAqtqqJ0yb20M1q+6E20d9BPNZ3k4yqmNBHfE3Rg6e0zQIDAQAB
AoGAY4C/HgECZV8wzyLXgUb8B6vw8TvyvJpaOUsF1y+l7Mw0TyXfnYdfhQ+e+po3
Nu+gjsGGc5HYh07Wl82ZWLSHf824wHVrZBNhIQ41/9p03MnJYgnXm0iU9zKQUpHq
xduwuRd65kTK6VRDvMF04AX4mX5XgDK/QhGD0kp7dZHcxmECQQDdIkTOGTEQFXDx
T+lwy0rKbZ8w2NlMotgIEOiXBl8YzZy6cVmQ8bgb2uRqrwdikuMEBAsktgv/AHbQ
+EnEFO95AkEAwvd72WTjs/MftbpunBHoD2NMRq4xQ3vsZgVmQWPu+ZJHRLRH/S1b
U4+edWECTm3OMWkex8quOZ3cVGwbnDM29QJAM0jCSkX/YeqHEf/ldLpA8ydvNuXj
p2lQzrYVKwlYPqlYwsiM7BeTCiq+tJs5DDxil8jUSMRLje0uoRIkEWyFiQJAbbuJ
M2Q7XI7cLBZQJvtVF0QWLDnidE8NUKZ9VRR/7mvMPPkTA4ZBvpg44WvGNopK4Th/
IIgM41TcK4/lQlb4+QJBALdIwPf6I5rHFqY18AiJ9xSB4IzM82aC8Iy1D4HFsaQD
FLDuG2n6YDKp8p97xaIHBmbCT8imwcMpdJ0bS6zYlrA=
-----END RSA PRIVATE KEY-----';
    private $remote_public_key_path = '';
    private $remote_public_key      = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs+JvfyTOMHqvjxHJyDZG
HZpz3atV7qcOT8mijXGGG3S+8Bb2p2kREGJwrzC2IIErCQUcZ3Wa3wTugKQDxqXE
SPt76HN2353ufegbvTI9kYgK0MLFpY8OZAMsaTytVrvUEVHjqGXZO4z7oVTqByuB
wcZAvK+sN39+MqisS6ZejACbbQLkWZgcSgt5wBAaDaEa2lvRYcVbNyO/mqTU6SSf
d+w78uM07BpmxhimOMwf+l/qs+Z04LUm4Ay7b+AHHAwbaHeehC1wInzNDfipgR0H
0FCa/LOnEblj2HVpptB/NY4XNG+CDHTBKkxzEw92D/Nj1JIlr1oP0l+/VdAnxxiW
uQIDAQAB
-----END PUBLIC KEY-----';
    private $localPrivateKey        = null;
    private $remotePublicKey        = null;
    private $_array_card            = array(
        'vina'    => 'VNP',
        'mobi'    => 'VMS',
        'vcoin'   => 'VTC',
        'gate'    => 'FPT',
        'viettel' => 'VTT',
        'mega'    => 'MGC',
        'oncash'  => 'ONC'
    );
    public $_promotion              = array(
        'vina'    => 0,
        'mobi'    => 0,
        'vcoin'   => 0,
        'gate'    => 0,
        'viettel' => 0,
        'mega'    => 0,
        'oncash'  => 0
    );
    protected $encryption           = [
        'init_vector' => true, 'algo'        => MCRYPT_3DES, 'mode'        => MCRYPT_MODE_ECB
    ];
    public $info_card;
    public $note;
    private $_error_card            = array(
        "-24" => "Dữ  liệu thẻ không đúng",
        "-11" => "Nhà cung cấp không tồn tại",
        "-10" => "Mã thẻ sai định dạng",
        "0"   => "Giao dịch thất bại",
        "1"   => "Giao dịch thành công",
        "3"   => "Sai session",
        "4"   => "Thẻ không sử dụng được",
        "5"   => "Nhập sai mã thẻ quá 5 lần",
        "7"   => "Session hết hạn",
        "8"   => "Sai IP",
        "9"   => "Hệ thống đang quá tải",
        "10"  => "Hệ thống nhà cung cấp dịch vụ đang có lỗi",
        "11"  => "Kết nối với nhà cung cấp dịch vụ tạm gián đoạn",
        "12"  => "Trùng Transaction",
        "13"  => "Hệ thống đang bận",
        "-2"  => "Thẻ bị khóa",
        "-3"  => "Thẻ hết hạn sử dụng",
        "50"  => "Thẻ đã sử dụng hoặc không tồn tại",
        "51"  => "Seri không đúng",
        "52"  => "Seri và mã thẻ không khớp",
        "53"  => "Seri hoặc mã thẻ không đúng",
        "55"  => "Thẻ tạm thời bị khóa 24 tiếng",
        "62"  => "Sai mật khẩu",
        "57"  => "Sai mpin",
        "58"  => "Sai tham số đầu vào",
        "59"  => "Mã thẻ chưa được kích hoạt",
        "60"  => "Sai partner ID",
        "61"  => "Sai user",
        "56"  => "Account tạm bị khóa do charging sai nhiều lần",
    );

    protected function _doCharge()
    {
        //Set Key
        $this->loadKeys();
        $this->cardData = $this->seri . ':' . $this->pin . ':0:' . $this->_array_card[$this->card_type];
        $soap_client    = new \SoapClient($this->url, [
            'trace' => 1
        ]);

        //Login Get Session
        $this->login();

        //Encrypt Key
        $key           = $this->hex2str($this->sessionId);
        $result        = static::encryptAes($this->mpin, $key, false);
        $encryptedMpin = bin2hex($result['data']);

        // Encrypt card data
        $result            = static::encryptAes($this->cardData, $key, false);
        $encryptedCardData = bin2hex($result['data']);
        $this->transaction = $this->merchant_code . $this->transaction;
        $result            = $soap_client->cardCharging(
                // Transaction ID
                $this->transaction,
                // Merchant account username
                $this->username,
                // Merchant ID/partner ID
                $this->merchant_id,
                // Encrypted MPIN
                $encryptedMpin,
                // Target account (optional)
                $this->user["ch_public_id"],
                // Encrypted PIN
                $encryptedCardData,
                // Session ID
                md5($this->sessionId));

//        if ($this->user["ch_public_id"] == "f23741423013342")
//        {
//            print_r($result);
//            die;
//        }
        $status = $result->{"status"};
        if ($status == 1)
        {
            $this->msg       = $this->_error_card[$status];
            $this->info_card = $this->decryptAes($this->hex2str($result->responseamount), $key);
        }
        else
        {
            $this->msg       = $this->_error_card[$status];
            $this->info_card = 0;
        }
        if (!empty($result->transid))
        {
            $this->transaction = $result->transid;
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

    protected function loadKeys()
    {
        $path = isset($this->local_private_key_path) ? $this->local_private_key_path : null;

        // Load key content
        $key = null;
        if (!empty($path))
        {
            $key = file_get_contents($path);
        }
        else
        {
            $key = isset($this->local_private_key) ? $this->local_private_key : null;
        }

        if (!empty($key))
        {
            $this->localPrivateKey = openssl_pkey_get_private($key);
        }

        $path = isset($this->remote_public_key_path) ? $this->remote_public_key_path : null;

        // Load key content
        $key = null;
        if (!empty($path))
        {
            $key = file_get_contents($path);
        }
        else
        {
            $key = isset($this->remote_public_key) ? $this->remote_public_key : null;
        }

        if (!empty($key))
        {
            $this->remotePublicKey = openssl_pkey_get_public($key);
        }
    }

    public static function padPkcs5($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function unpadPkcs5($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
        {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
        {
            return false;
        }

        return substr($text, 0, - 1 * $pad);
    }

    public static function createKey($length)
    {
        $key     = '';
        $replace = array(
            '/', '+', '='
        );

        while (strlen($key) < $length)
        {
            $key .= str_replace($replace, NULL, base64_encode(mcrypt_create_iv($length, MCRYPT_RAND)));
        }

        return substr($key, 0, $length);
    }

    public function encryptAes($input, $key = null, $encodeBase64 = true)
    {
        if (!isset($this->cipher) || !$this->cipher)
        {
            $this->encryption['block_size'] = mcrypt_get_block_size(
                    $this->encryption['algo'], $this->encryption['mode']);
            $this->cipher                   = mcrypt_module_open($this->encryption['algo'], '', $this->encryption['mode'], '');

            // Returns the cipher's largest key size in BYTES.
            $this->encryption['key_size'] = mcrypt_get_key_size($this->encryption['algo'], $this->encryption['mode']);
        }

        $iv = null;
        if ($this->encryption['init_vector'])
        {
            $sz = mcrypt_enc_get_iv_size($this->cipher);
            $iv = mcrypt_create_iv($sz, MCRYPT_RAND);
        }
        else
        {
            // Empty IV - initialization vector
            $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
        }

        if (!$key)
        {
            $key = static::createKey($this->encryption['key_size']);
        }

        $len = strlen($key);
        if ($len != $this->encryption['key_size'])
        {
            throw new \RuntimeException(
            'Key \'' . $key . '\' has invalid key size=' . $len . ', expected ' .
            $this->encryption['key_size']);
        }

        // This is the definition of PKCS#5 padding (6.2):
        // The padding string PS shall consist of 8 - (||M|| mod 8) octets all having value 8 - (||M|| mod 8).
        $input = static::padPkcs5($input, 8);

        $bin           = pack('H*', bin2hex($input));
        $encryptedData = mcrypt_encrypt($this->encryption['algo'], $key, $bin, $this->encryption['mode'], $iv);

        // Base64 encode
        if ($encodeBase64 === true)
        {
            return [
                'key'  => base64_encode($key), 'data' => base64_encode($encryptedData),
                'iv'   => base64_encode($iv)
            ];
        }

        return [
            'key'  => $key, 'data' => $encryptedData, 'iv'   => $iv
        ];
    }

    public function decryptAes($input, $key, $iv = null)
    {
        if ($this->encryption['init_vector'] && !$iv)
        {
            $sz = mcrypt_enc_get_iv_size($this->cipher);
            $iv = mcrypt_create_iv($sz, MCRYPT_RAND);
        }

        $decryptedData = mcrypt_decrypt($this->encryption['algo'], $key, $input, $this->encryption['mode'], $iv);
        return rtrim(static::unpadPkcs5($decryptedData));
    }

    public function decryptLocalPrivateKey($input)
    {
        // Gponster <anhvudg@gmail.com> WTF with VNPT Epay, this is a kind of chunked encrypt haha...???
        $parts  = explode(':::', $input);
        $cnt    = count($parts);
        $i      = 0;
        $output = '';

        $decrypted = null;
        while ($i < $cnt)
        {
            openssl_private_decrypt($parts[$i], $decrypted, $this->localPrivateKey);
            $output .= $decrypted;
            $i ++;
        }

        return $output;
    }

    public function encryptRemotePublicKey($input, $encodeBase64 = true)
    {
        $encryptedBytes = null;

        openssl_public_encrypt($input, $encryptedBytes, $this->remotePublicKey);

        $output = $encryptedBytes;
        if ($encodeBase64)
        {
            $output = base64_encode($encryptedBytes);
        }

        return $output;
    }

    protected function login()
    {
        $this->sessionId = null;

        // Create encrypted password
        $encryptedPassword = $this->encryptRemotePublicKey($this->password);
        $soap_client       = new \SoapClient($this->url, [
            'trace' => 1
        ]);
        $result            = $soap_client->login($this->username, $encryptedPassword, $this->merchant_id);
        $error             = null;
        $status            = $result->{"status"};

        $decrypted = null;
        if ($status == 1)
        {
            $decrypted = $this->decryptLocalPrivateKey(base64_decode($result->{"sessionid"}));

            // Response decrypted in hexa string format
            $this->sessionId = bin2hex($this->hex2str($decrypted));
        }
        else
        {
            $error = isset($this->_error_card[$status]) ? $this->_error_card[$status] : null;
        }
    }

    public static function hex2str($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2)
        {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }

    public function checkCard($serial)
    {
        
    }

}
