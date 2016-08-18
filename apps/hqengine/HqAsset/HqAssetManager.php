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

namespace HqEngine\HqAsset;

use HqEngine\HqTool\HqUtil as Util;
use HqEngine\HqDI\HqDIBehaviour;
use HqEngine\HqException;
use HqEngine\HqTool\HqSystemTool as SystemTool;
use Phalcon\Assets\Collection;
use Phalcon\Assets\Filters\Cssmin;
use Phalcon\Assets\Filters\Jsmin;
use Phalcon\Assets\Manager as AssetManager;
use Phalcon\Cache\Backend;
use Phalcon\Config;
use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Tag;

class HqAssetManager extends AssetManager {

    const
    /**
     * Style file name in url.
     */
            FILENAME_PATTERN_CSS = 'style-%s.css',
            /**
             * Javascript file name in url.
             */
            FILENAME_PATTERN_JS = 'javascript-%s.js';
    const
    /**
     * Javascript default collection name.
     */
            DEFAULT_COLLECTION_JS = 'js',
            /**
             * CSS default collection name.
             */
            DEFAULT_COLLECTION_CSS = 'css';
    const

    /**
     * Generated path for files that will be merged and minified.
     */
            GENERATED_STORAGE_PATH = 'gen/';

    /**
     * Application config.
     *
     * @var Config
     */
    protected $_config;

    /**
     * Cache.
     *
     * @var Backend
     */
    protected $_cache;

    /**
     * Inline <head> code.
     *
     * @var array
     */
    protected $_inline = [];

    use \HqEngine\HqDI\HqDIBehaviour;

    public function __construct($di, $prepare = true)
    {
        $this->createDI($di);
        $this->_config = $di->getConfig();
        $this->_config = $di->getConfig();
        $this->_cache  = $di->getCacheData();
        if ($prepare)
        {
            $this->set(self::DEFAULT_COLLECTION_CSS, $this->getEmptyCssCollection());
            $this->set(self::DEFAULT_COLLECTION_JS, $this->getEmptyJsCollection());
        }
    }

    /**
     * Install assets from all modules.
     *
     * @param string $themeDirectory Theme directory.
     *
     * @return void
     */
    public function installAssets($themeDirectory = 'default')
    {
        $location = $this->_getLocation();
        Util::checkFile($location, true);
        Util::checkFile($location . self::GENERATED_STORAGE_PATH, true);
        ///////////////////////////////////
        // Compile themes css.
        ///////////////////////////////////
        Util::checkFile($location . 'css/', true);
        Util::checkFile($location . 'js/', true);
        Util::checkFile($location . 'img/', true);

        ///////////////////////////////////
        // Collect css/js/img from modules.
        ///////////////////////////////////
        $registry = $this->getDI()->get('registry');
        foreach ($registry->modules as $module)
        {
            $assetsPath = $registry->directories->modules . ucfirst($module) . '/assets/' . $themeDirectory;
            // CSS
            $path       = $location . $module . '/' . $themeDirectory . "/css/";
            Util::checkFile($path, true);
            SystemTool::copyRecursive($assetsPath . 'css', $path, true);

            // JS
            $path = $location . $module . '/' . $themeDirectory . "/js/";
            Util::checkFile($path, true);
            SystemTool::copyRecursive($assetsPath . 'js', $path, true);

            // IMAGES
            $path = $location . $module . '/' . $themeDirectory . "/img/";
            Util::checkFile($path, true);
            SystemTool::copyRecursive($assetsPath . 'img', $path, true);
        }
    }

    /**
     * Clear assets cache.
     *
     * @param bool   $refresh        Install and compile new assets?
     * @param string $themeDirectory Theme directory.
     *
     * @return void
     */
    public function clear($refresh = true, $themeDirectory = 'default')
    {
        $location = $this->_getLocation();
        $files    = SystemTool::recursiveGlob($location, '*'); // get all file names
        // iterate files
        foreach ($files as $file)
        {
            if (is_file($file))
            {
                @unlink($file); // delete file
            }
        }

        if ($refresh)
        {
            $this->installAssets($themeDirectory);
        }
    }

    /**
     * Get empty JS collection.
     *
     * @return Collection
     */
    public function getEmptyJsCollection()
    {
        $collection = new Collection();
        return $collection;
    }

    /**
     * Get empty CSS collection.
     *
     * @return Collection
     */
    public function getEmptyCssCollection()
    {
        $collection = new Collection();
        return $collection;
    }

    /**
     * Add <head> inline code.
     *
     * @param string $name Identification.
     * @param string $code Code to add to <head> tag.
     *
     * @return $this
     */
    public function addInline($name, $code)
    {
        $this->_inline[$name] = $code;
        return $this;
    }

    /**
     * Remove inline code.
     *
     * @param string $name Identification.
     *
     * @return $this
     */
    public function removeInline($name)
    {
        unset($this->_inline[$name]);
        return $this;
    }

    /**
     * Get <head> tag inline code.
     *
     * @return string
     */
    public function outputInline(Collection $collection, $type)
    {
        return implode('\n', $this->_inline);
    }

    /**
     * Prints the HTML for JS resources.
     *
     * @param string $collectionName the name of the collection
     *
     * @return string
     * */
    public function outputJs($collectionName = self::DEFAULT_COLLECTION_JS)
    {
        $collection = $this->collection($collectionName);
        $remote     = $this->_config->assets->get('remote');
        if ($remote)
        {
            $collection
                    ->setPrefix($this->_config->assets->get('cdn'))
                    ->setLocal(false);
        }
        else if ($this->_config->assets->join)
        {
            $collection
                    ->addFilter(new Jsmin())
                    ->join(true);
        }
        if (!$remote && $collection->getJoin())
        {
            $local    = $this->_config->assets->get('local');
            $lifetime = $this->_config->assets->get('lifetime');
            $filename = $this->getCollectionFileName($collection, self::FILENAME_PATTERN_JS);
            $filepath = $local . self::GENERATED_STORAGE_PATH . $filename;
//            $collection
//                    ->setTargetPath($filepath)
//                    ->setTargetUri($filepath);

            if ($this->_cache->exists($filename))
            {
                return Tag::javascriptInclude($collection->getTargetUri());
            }
            $res = parent::outputJs($collectionName);
//            $this->_cache->save($filename, true, $lifetime);
            return $res;
        }
        return parent::outputJs($collectionName);
    }

    /**
     * Prints the HTML for CSS resources.
     *
     * @param string $collectionName the name of the collection
     *
     * @return string
     * */
    public function outputCss($collectionName = self::DEFAULT_COLLECTION_CSS)
    {
        $remote     = $this->_config->assets->get('remote');

        $collection = $this->collection($collectionName);
        if ($remote)
        {
            $collection
                    ->setPrefix($this->_config->assets->get('cdn'))
                    ->setLocal(false);
        }
        else if ($this->_config->assets->join)
        {

            $collection
                    ->addFilter(new Cssmin())
                    ->join($this->_config->assets->join);
        }
        if (!$remote && $collection->getJoin())
        {
            $local    = $this->_config->assets->get('local');
            $lifetime = $this->_config->assets->get('lifetime');
            $filename = $this->getCollectionFileName($collection, self::FILENAME_PATTERN_CSS);
            $filepath = $local . self::GENERATED_STORAGE_PATH . $filename;
//            $collection
//                    ->setTargetPath(ROOT_PATH . $filepath)
//                    ->setTargetUri($filepath);
            if ($this->_cache->exists($filename))
            {
                return Tag::stylesheetLink($collection->getTargetUri());
            }
            $res = parent::outputCss($collectionName);
//            $this->_cache->save($filename, true, $lifetime);
            return $res;
        }
        return parent::outputCss($collectionName);
    }

    /**
     * Get file name by collection using pattern.
     *
     * @param Collection $collection Asset collection.
     * @param string     $pattern    File name pattern.
     *
     * @return string
     */
    public function getCollectionFileName(Collection $collection, $pattern)
    {
        return sprintf($pattern, crc32(serialize($collection)));
    }

    /**
     * Get location according to params.
     * Without params - just full path to assets directory.
     *
     * @param null|string $filename Filename append to assets path.
     *
     * @return string
     */
    protected function _getLocation($filename = null)
    {
        $location = PUBLIC_PATH . '/' . $this->_config->assets->get('local');
        if (!$filename)
        {
            return $location;
        }

        return $location . '/' . $filename;
    }

}
