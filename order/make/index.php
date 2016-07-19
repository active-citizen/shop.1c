<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("bitrix:sale.order.ajax", "ag", array(
	"PAY_FROM_ACCOUNT" => "Y",
    "DELIVERY_TO_PAYSYSTEM"=>"p2d",
    "ONLY_FULL_PAY_FROM_ACCOUNT"=>"Y",
	"COUNT_DELIVERY_TAX" => "N",
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
	"ALLOW_AUTO_REGISTER" => "Y",
	"SEND_NEW_USER_NOTIFY" => "Y",
	"DELIVERY_NO_AJAX" => "N",
	"TEMPLATE_LOCATION" => "popup",
	"PROP_1" => array(
	),
	"PATH_TO_BASKET" => "/cart/",
	"PATH_TO_PERSONAL" => "/order/",
	"PATH_TO_PAYMENT" => "/order/payment/",
	"PATH_TO_ORDER" => "/order/make/",
	"SET_TITLE" => "Y" ,
	"SHOW_ACCOUNT_NUMBER" => "Y",
	"DELIVERY_NO_SESSION" => "Y",
	"COMPATIBLE_MODE" => "N",
	"BASKET_POSITION" => "before",
	"BASKET_IMAGES_SCALING" => "adaptive",
	"SERVICES_IMAGES_SCALING" => "adaptive"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>