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
		"CONDITION" => "#^//profileorder/#",
		"RULE" => "",
		"ID" => "bitrix:sale.personal.order",
		"PATH" => "/profile/order/index.php",
	),
    array(
        "CONDITION" => "#^/profile/order/#",
        "RULE" => "",
        "ID" => "bitrix:sale.personal.order",
        "PATH" => "/profile/order/index.php",
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
        "CONDITION" => "#^/profile/points/#",
        "RULE" => "",
        "ID" => "ag:points",
        "PATH" => "/profile/points/index.php",
    ),
);

?>
