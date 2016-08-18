<?php

define("PHP_VERSION_REQUIRED", "5.4");
define("PHALCON_VERSION_REQUIRED", "2.0.1");


if (!defined('CHECK_REQUIREMENTS'))
{
    die('Access denied!');
}

if (version_compare(phpversion(), PHP_VERSION_REQUIRED, '<'))
{
    printf('PHP %s is required, you have %s.', PHP_VERSION_REQUIRED, phpversion());
    exit(1);
}
if (version_compare(phpversion('phalcon'), PHALCON_VERSION_REQUIRED, '<'))
{
    printf('Phalcon Framework %s is required, you have %s.', PHALCON_VERSION_REQUIRED, phpversion('phalcon'));
    exit(1);
}
if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules()))
{
    print('Apache "mod_rewrite" module is required!');
    exit(1);
}

$checkPath = array(
    $this->_config->assets->local,
    $this->_config->logger->path,
    $this->_config->cache->get('cacheDir') ? $this->_config->cache->cacheDir : null,
    $this->_config->view->compiledPath,
    $this->_config->metadata->metaDataDir,
    $this->_config->annotations->annotationsDir,
    ROOT_PATH . '/apps/data/cache/languages/',
    ROOT_PATH . '/apps/data/temp'
);

$GLOBALS['PATH_REQUIREMENTS'] = $checkPath;

$allPassed = true;

foreach ($checkPath as $path)
{
    if ($path === null)
    {
        continue;
    }
    \HqEngine\HqTool\HqUtil::checkFile($path, true);
    $is_writable = is_writable($path);
    if (!$is_writable)
    {
        echo "{$path} isn't writable.</br>";
    }

    $allPassed = $allPassed && $is_writable;
}

if (!$allPassed)
{
    exit(1);
}
