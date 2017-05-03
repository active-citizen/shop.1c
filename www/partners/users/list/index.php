<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Часто задаваемые вопросы");
?>
<div class="partners-main">
    <? include("../../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:partners.users.list","",array(
        "CACHE_TIME"=>1
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
