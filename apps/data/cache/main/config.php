<?php
            
        return array (
  'web_name' => 'Admin Trung Tâm Anh Ngữ',
  'front_name' => ' - Trung Tâm Anh Ngữ',
  'back_name' => ' - Backend - Admin Trung Tâm Anh Ngữ',
  'acl' => false,
  'profiler' => false,
  'baseUrl' => '/',
  'rootUrl' => 'http://trungtamanhngu.dev:8080/',
  'router' => 
  array (
    'default_module' => 'frontend',
  ),
  'cache' => 
  array (
    'lifetime' => 3153600000,
    'prefix' => 'hq_',
    'adapter' => 'File',
    'cacheDir' => ROOT_PATH . '/apps/data/cache/data/',
  ),
  'memcache' => 
  array (
    'lifetime' => 86400,
    'adapter' => 'Memcache',
    'host' => 'localhost',
    'port' => 11211,
    'persistent' => false,
  ),
  'logger' => 
  array (
    'enabled' => false,
    'path' => ROOT_PATH . '/apps/data/logs/',
    'format' => '[%date%][%type%] %message%',
  ),
  'view' => 
  array (
    'compiledPath' => ROOT_PATH . '/apps/data/cache/views/',
    'compiledExtension' => '.php',
    'compiledSeparator' => '_',
    'compileAlways' => false,
  ),
  'session' => 
  array (
    'adapter' => 'Files',
    'uniqueId' => 'Hq_',
  ),
  'memsession' => 
  array (
    'adapter' => 'Memcache',
    'host' => '127.0.0.1',
    'port' => 11211,
    'lifetime' => 86400,
    'persistent' => true,
    'prefix' => 'vatc_',
  ),
  'assets' => 
  array (
    'local' => '/public/',
    'cdn' => 'http://id.suregame.net/',
    'remote' => false,
    'lifetime' => 0,
    'join' => false,
  ),
  'metadata' => 
  array (
    'adapter' => 'Files',
    'metaDataDir' => ROOT_PATH . '/apps/data/cache/metadata/',
  ),
  'annotations' => 
  array (
    'adapter' => 'Files',
    'annotationsDir' => ROOT_PATH . '/apps/data/cache/annotations/',
  ),
  'modules' => 
  array (
    0 => 'frontend',
    1 => 'backend',
  ),
  'languages' => 
  array (
    'cacheDir' => ROOT_PATH . '/apps/data/cache/languages/',
    'list' => 
    array (
      'en' => 'en_us',
    ),
    'locale' => 'en_us',
    'language' => 'en',
  ),
  'database' => 
  array (
    'adapter' => 'Mysql',
    'host' => 'khoidongtuonglai.vatc.edu.vn',
    'username' => 'vatc_u',
    'password' => 'ukFlZbnT4D',
    'dbname' => 'vatc_db',
    'charset' => 'utf8',
    'port' => '3306',
  ),
  'config' => true,
  'events' => 
  array (
  ),
);