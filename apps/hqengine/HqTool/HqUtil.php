<?php

namespace HqEngine\HqTool;

class HqUtil {

    public static function checkFile($path, $dir = false)
    {
        if ($dir)
        {
            self::existDir($path);
        }
        else
        {
            $dir = dirname($path);
            self::existDir($dir);
            self::existFile($path);
        }
    }

    private static function existDir($path)
    {
        if (!is_dir($path))
        {
            mkdir($path, 0755, true);
        }
    }

    private static function existFile($path)
    {
        if (!file_exists($path))
        {
            $file = fopen($path, "w");
            fclose($file);
        }
    }

}
