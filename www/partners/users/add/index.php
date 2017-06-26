<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавить пользователя");
?>
<div class="partners-main">
    <? include("../../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:partners.users.add","",array(
        "CACHE_TIME"    =>  1,
        "BACK_URL"      =>  $_REQUEST["backurl"]
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
