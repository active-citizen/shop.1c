<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    CModule::IncludeModule("iblock");
    $APPLICATION->SetTitle(
        "Мои желания"
    );
    include("../menu.php");

?>

<? require($_SERVER["DOCUMENT_ROOT"]."/catalog/desktop.filter.params.php");?>
<? include_once($_SERVER["DOCUMENT_ROOT"]."/catalog/filter.inc.php");?>
<? include_once($_SERVER["DOCUMENT_ROOT"]."/catalog/container.inc.php");?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
