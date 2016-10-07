<?php

if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru'){
    $DB_HOST = 'localhost';
    $DB_NAME = 'ag_mos_ru';
    $DB_USER = 'ag_mos_ru';
    $DB_PASS = 'ag_mos_ru';
}
else{
    $DB_HOST = 'localhost';
    $DB_NAME = 'shop_ag_mos_ru';
    $DB_USER = 'shop_ag_mos_ru';
    $DB_PASS = 's6ehd_Jhgak';
}
return array (
  'utf_mode' => 
  array (
    'value' => true,
    'readonly' => true,
  ),
  'cache_flags' => 
  array (
    'value' => 
    array (
      'config_options' => 3600.0,
      'site_domain' => 3600.0,
    ),
    'readonly' => false,
  ),
  'cookies' => 
  array (
    'value' => 
    array (
      'secure' => false,
      'http_only' => true,
    ),
    'readonly' => false,
  ),
  'exception_handling' => 
  array (
    'value' => 
    array (
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => NULL,
    ),
    'readonly' => false,
  ),
  'connections' => 
  array (
    'value' => 
    array (
      'default' => 
      array (
        'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
        'host' => 'localhost',
        'database' => 'ag_mos_ru',
        'login' => 'ag_mos_ru',
        'password' => 'ag_mos_ru',
        'options' => 2.0,
      ),
    ),
    'readonly' => true,
  ),
);
