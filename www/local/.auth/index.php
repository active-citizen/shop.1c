<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки доступа");
?>
<div class="settings-wrap">
    <h1>Настройки доступа</h1>
    <? $APPLICATION->IncludeComponent("ag:settings", "", [
        "CODE" => "AUTH",
        "CACHE_TIME" => COMMON_CACHE_TIME
    ], false); ?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
