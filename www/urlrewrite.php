<?
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/bitrix/services/ymarket/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/bitrix/services/ymarket/index.php",
	),
	array(
		"CONDITION" => "#^/partners/orders/(\\d+)/.*#",
		"RULE" => "ID=\$1",
		"ID" => "ag:partners.orders.view",
		"PATH" => "/partners/orders/order.php",
	),
	array(
		"CONDITION" => "#^/rules/faq/(\\d+)/.*#",
		"RULE" => "SECTION_ID=\$1",
		"ID" => "ag:faq_sectioned",
		"PATH" => "/rules/faq/index.php",
	),
	array(
		"CONDITION" => "#^/partners/download/#",
		"RULE" => "",
		"ID" => "ag:partners.orders.download",
		"PATH" => "/partners/orders/download.php",
	),
	array(
		"CONDITION" => "#^/stssync/calendar/#",
		"RULE" => "",
		"ID" => "bitrix:stssync.server",
		"PATH" => "/bitrix/services/stssync/calendar/index.php",
	),
	array(
		"CONDITION" => "#^/profile/points/#",
		"RULE" => "",
		"ID" => "ag:points",
		"PATH" => "/profile/points/index.php",
	),
	array(
		"CONDITION" => "#^/profile/order/#",
		"RULE" => "",
		"ID" => "ag:orders",
		"PATH" => "/profile/order/index.php",
	),
	array(
		"CONDITION" => "#^/rules/stores/#",
		"RULE" => "",
		"ID" => "bitrix:catalog.store",
		"PATH" => "/rules/stores/index.php",
	),
	array(
		"CONDITION" => "#^/catalog/\$#",
		"RULE" => "",
		"ID" => "bitrix:catalog",
		"PATH" => "/catalog/index.php",
	),
	array(
		"CONDITION" => "#^/catalog/#",
		"RULE" => "",
		"ID" => "bitrix:catalog",
		"PATH" => "/catalog/catalog.php",
	),
	array(
		"CONDITION" => "#^/news/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => "/news/index.php",
	),
);

?>
