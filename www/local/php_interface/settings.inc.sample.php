<?php
/*
    Настройки
*/
// Мочтовый адрес магазина
define("SHOP_EMAIL", "");
// Количество заказов, которые выгружаются в 1С за один приём
define("ORDER_EXPORT_QUANT", 10);
// Каталог с почтовыми шаблонами
define("MAIL_TMPL_PATH", realpath(dirname(__FILE__) . "/../mail_templates/"));

// List of domains, adresses and coutours
// Each element means
// array(
//      'domain',
//      'datacenter internal IP',
//      'external IP',
//      'crossdomain authorization URL',
//      'bals conversion controller URL',
//      'countour name',
//  )
$arContours = [
    [
        "domain" => "",
        "int_ip" => "",
        "ext_ip" => "",
        "auth_url" => "",
        "bcc_url" => "",
        "name" => "prod"
    ],
    [
        "domain" => "",
        "int_ip" => "",
        "ext_ip" => "",
        "auth_url" => "",
        "bcc_url" => "",
        "name" => "uat"
    ],
    [
        "domain" => "",
        "int_ip" => "",
        "ext_ip" => "",
        "auth_url" => "",
        "bcc_url" => "",
        "name" => "test"
    ],
    [
        "domain" => "",
        "int_ip" => "",
        "ext_ip" => "",
        "auth_url" => "",
        "bcc_url" => "",
        "name" => "test"
    ],
];


// Значения по умолчанию
$sContour = 'test';
$sCrossDomainAuthURL = '';
$sBCCUrl = '';

// Определяемся на каком контуре находимся
foreach ($arContours as $arContour) {
    if ($arContour["domain"] == $_SERVER["HTTP_HOST"] || $arContour["int_ip"] == $_SERVER["HTTP_HOST"]) {
        $sContour = $arContour["name"];
        $sCrossDomainAuthURL = $arContour["auth_url"];
        $sBCCUrl = $arContour["bcc_url"];
        $sMainUrl = $arContour["domain"];
        break;
    }
}
// Define current countour
define("CONTOUR", $sContour);
// Define contour URL
define("CONTOUR_URL", $sCrossDomainAuthURL);
// Define Bals Conversion Controller
define("BCC_URL", $sBCCUrl);
define("MAIN_DOMAIN", $sMainUrl);

//
if (preg_match("#^/bitrix/admin#", $_SERVER["REQUEST_URI"])) {
    define("ORDERS_EXCHANGE_ADMIN_MODE", true);
} else {
    define("ORDERS_EXCHANGE_ADMIN_MODE", false);
}

