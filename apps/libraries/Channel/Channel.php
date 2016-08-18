<?php

namespace HqLibrary\Channel;

class Channel {

    public $_id        = "";
    public $_name      = "";
    public $_full_name = "";
    public $_short     = "";
    protected $_key    = "";

    public function checkKey($params = array())
    {
        if (isset($params["key"]))
        {
            $key        = $params["key"];
            unset($params["key"]);
            unset($params["_url"]);
            ksort($params);
            $_array_key = implode(",", array_keys($params));
            $_array     = implode(",", $params);
            $check_key  = md5($_array_key . $_array . $this->_key);
            if ($check_key == $key)
            {
                return true;
            }
        }
        return false;
    }

}
