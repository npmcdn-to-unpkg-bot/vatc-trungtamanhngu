<?php

/*
  +------------------------------------------------------------------------+
  | PhalconEye CMS                                                         |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-2014 PhalconEye Team (http://phalconeye.com/)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconeye.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Ivan Vorontsov <ivan.vorontsov@phalconeye.com>                 |
  +------------------------------------------------------------------------+
 */

namespace HqEngine\HqLanguage;

class HqLanguage {

    public $_di;
    public $_config;
    public $_module_config;
    public $_session;
    public $_language_file;
    public $_languages;

    public function __construct($di, $module_config)
    {
        $this->_di            = $di;
        $this->_config        = $di->get("config");
        $this->_module_config = $module_config;
        $this->_session       = $di->get("session");
        $this->_languages     = $this->getLanguage();
    }

    public function __($text)
    {
        $result = $text;
        if (isset($this->_languages[$text]) && !empty($this->_languages[$text]) && strlen($this->_languages[$text]) > 0)
        {
            $result = $this->_languages[$text];
        }
        else
        {
            $this->_languages[$text] = $text;
            file_put_contents($this->_language_file, $this->_toString($this->_languages));
        }
        return $result;
    }

    public function _e($text)
    {
        echo ($this->__($text));
    }

    public function getLanguage()
    {
        $language_dir         = $this->_di->get('registry')->directories->modules . $this->_module_config->name . "/languages";
        \HqEngine\HqTool\HqUtil::checkFile($language_dir, true);
        $nation               = $this->_session->get("language");
        $nation_dir           = $language_dir . "/" . $nation;
        \HqEngine\HqTool\HqUtil::checkFile($nation_dir, true);
        $locate               = $this->_session->get("locale");
        $language_file        = $nation_dir . "/" . $locate . ".php";
        \HqEngine\HqTool\HqUtil::checkFile($language_file);
        $this->_language_file = $language_file;
        $language             = include_once ($language_file);
        if ($language == 1)
        {
            $language = array();
        }
        return $language;
    }

    protected function _toString($data = null)
    {
        if (!$data)
        {
            $data = $this->toArray();
        }
        $language_text = var_export($data, true);

        // Fix pathes. This related to windows directory separator.
        $language_text = str_replace('\\\\', DS, $language_text);
        $headerText    = '<?php
            
        return ';
        return $headerText . $language_text . ';';
    }

}
