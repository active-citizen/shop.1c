<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("ag:sale.personal.order", "", array(
    "SEF_MODE" => "Y",
    "SEF_FOLDER" => "/order/",
    "ORDERS_PER_PAGE" => "10",
    "PATH_TO_PAYMENT" => "/order/payment/",
    "PATH_TO_BASKET" => "/cart/",
    "SET_TITLE" => "Y",
    "SAVE_IN_SESSION" => "N",
    "NAV_TEMPLATE" => "arrows",
    "SEF_URL_TEMPLATES" => array(
        "list" => "index.php",
        "detail" => "detail/#ID#/",
        "cancel" => "cancel/#ID#/",
    ),
    "SHOW_ACCOUNT_NUMBER" => "Y"
    ),
    false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>