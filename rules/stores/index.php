<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Центры выдачи");

include("../menu.php")

?>
<h1>Где получить</h1>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.store",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PHONE" => "N",
		"SCHEDULE" => "N",
		"SET_TITLE" => "Y",
		"TITLE" => "Список центров выдачи с подробной информацией",
		"MAP_TYPE" => "0",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"SEF_FOLDER" => "/rules/stores/",
		"SEF_URL_TEMPLATES" => Array(
			"liststores" => "index.php",
			"element" => "#store_id#"
		),
		"VARIABLE_ALIASES" => Array(
			"liststores" => Array(),
			"element" => Array(),
		)
	),
false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>