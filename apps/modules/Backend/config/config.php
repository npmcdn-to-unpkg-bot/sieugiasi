<?php

return new \Phalcon\Config(array(
    'name' => 'Backend',
    'main_route' => 'backend',
    'database' => array(
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'sieugiasi',
        'charset' => 'utf8',
        'port' => '3306'
    ),
        ));
