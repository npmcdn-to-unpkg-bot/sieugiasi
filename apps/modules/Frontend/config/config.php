<?php

return new \Phalcon\Config(array(
    'name' => 'Frontend',
    'main-route' => "frontend",
    'database' => array(
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'sieugiasi',
        'charset' => 'utf8',
        'port' => '3306'
    ),
    "FACEBOOK_URL" => "https://www.facebook.com/DEmodemo",
    "FACEBOOK_ID" => "586112871477485",
    "FACEBOOK_SECRECT" => "e716bfdbfe4bbe48ebc8c339ab15584d",
    "GOOGLE_NAME"=>"NamTrung",
    "GOOGLE_ID" => "808425805635-vmqith1ui6cn83frc83rbni01ns540g4.apps.googleusercontent.com",
    "GOOGLE_API_KEY" => "q6LUKzgWKPqiE-I_o5Dj3D09",
));
