<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавить пользователя");
require("../../group_access.php");
?>
<div class="partners-main">
    <? include("../../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:partners.users.delete","",array(
        "CACHE_TIME"    =>  1,
        "PAGE_NUM"      =>  isset($_REQUEST["PAGEN_1"])?$_REQUEST["PAGEN_1"]:1,
        "RECORDS_ON_PAGE"=> 20
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
