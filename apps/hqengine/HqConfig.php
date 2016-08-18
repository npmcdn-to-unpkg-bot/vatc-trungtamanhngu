<?php

namespace HqEngine;

class HqConfig extends \Phalcon\Config {

    const
            CONFIG_PATH = "/apps/config",
            CONFIG_CACHE_PATH = '/apps/data/cache/main/config.php',
            CONFIG_DEFAULT_LANGUAGE = 'en',
            CONFIG_DEFAULT_LOCALE = 'en_us',
            CONFIG_METADATA_APP = '/apps/data/metadata/main/app.php';

    public function __construct($array_config = null)
    {
        if (empty($array_config))
        {
            $array_config = include (ROOT_PATH . self::CONFIG_PATH . '/config.php');
        }
        parent::__construct($array_config);
    }

    public static function getConfig()
    {
        $new_config = true;
        $cache_path = ROOT_PATH . self::CONFIG_CACHE_PATH;
        if (file_exists($cache_path))
        {
            $config_cache = include_once($cache_path);
            if (!empty($config_cache) && is_array($config_cache))
            {
                $config     = new HqConfig($config_cache);
                $new_config = true;
            }
        }
        $new_config = true;
        if ($new_config)
        {
            $config = self::_getConfiguration();
            $config->refreshCache();
        }
        return $config;
    }

    public function refreshCache()
    {
        $path = ROOT_PATH . self::CONFIG_CACHE_PATH;
        $dir  = dirname($path);
        if (!file_exists($path))
        {
            if (!is_dir($dir))
            {
                mkdir($dir, 0755, true);
            }
            $file = fopen($path, "w");
            fclose($file);
        }
        file_put_contents($path, $this->_configToString());
    }

    protected function _configToString($data = null)
    {
        if (!$data)
        {
            $data = $this->toArray();
        }
        $configText = var_export($data, true);

        // Fix pathes. This related to windows directory separator.
        $configText = str_replace('\\\\', DS, $configText);

        $configText = str_replace("'" . PUBLIC_PATH, "PUBLIC_PATH . '", $configText);
        $configText = str_replace("'" . ROOT_PATH, "ROOT_PATH . '", $configText);
        $headerText = '<?php
            
        return ';
        return $headerText . $configText . ';';
    }

    public function save()
    {
        $configDirectory = ROOT_PATH . self::CONFIG_PATH;
        file_put_contents(
                $configDirectory . '/config.php', $this->configToString($this->get("config")->toArray())
        );
        $this->refreshCache();
    }

    protected static function _getConfiguration()
    {
        $config          = new HqConfig(null);
        $configDirectory = ROOT_PATH . self::CONFIG_PATH;
        $global_config   = include_once($configDirectory . '/config.php');
        $config->offsetSet(basename("config"), $global_config);
        $appPath         = ROOT_PATH . self::CONFIG_METADATA_APP;

        if (!file_exists($appPath))
        {
            $config->offsetSet('events', array());
//            $config->offsetSet('modules', array());
            return $config;
        }

        $data = include_once($appPath);
        $config->merge(new HqConfig($data));
        return $config;
    }

}
