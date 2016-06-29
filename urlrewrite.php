<?
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/bitrix/services/ymarket/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/bitrix/services/ymarket/index.php",
	),
    array(
        "CONDITION" => "#^/catalog/$#",
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
		"CONDITION" => "#^/order/#",
		"RULE" => "",
		"ID" => "bitrix:sale.personal.order",
		"PATH" => "/order/index.php",
	),
    array(
        "CONDITION" => "#^/order/#",
        "RULE" => "",
        "ID" => "bitrix:sale.personal.order",
        "PATH" => "/order/index.php",
    ),
	array(
		"CONDITION" => "#^/rules/stores/#",
		"RULE" => "",
		"ID" => "bitrix:catalog.store",
		"PATH" => "/rules/stores/index.php",
	),
	array(
		"CONDITION" => "#^/news/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => "/news/index.php",
	),
    array(
        "CONDITION" => "#^/points/#",
        "RULE" => "",
        "ID" => "ag:points",
        "PATH" => "/points/index.php",
    ),
);

?>