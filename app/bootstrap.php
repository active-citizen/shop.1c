<?php

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../www/bitrix/header.php');

$loader = new \Composer\Autoload\ClassLoader();
$loader->addPsr4('app\\', __DIR__);
$loader->register();