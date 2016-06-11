<?php
            
        return array (
  'web_name' => 'Admin Sieu Gia Si',
  'front_name' => ' - Sieu Gia Si',
  'back_name' => ' - Backend - Admin Sieu Gia Si',
  'acl' => false,
  'profiler' => false,
  'baseUrl' => '/sieugiasi/',
  'rootUrl' => 'http://localhost/sieugiasi/',
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
    'enabled' => true,
    'path' => ROOT_PATH . '/apps/data/logs/',
    'format' => '[%date%][%type%] %message%',
  ),
  'view' => 
  array (
    'compiledPath' => ROOT_PATH . '/apps/data/cache/views/',
    'compiledExtension' => '.php',
    'compiledSeparator' => '_',
    'compileAlways' => true,
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
    'prefix' => 'Hq_',
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
  'config' => true,
  'events' => 
  array (
  ),
);