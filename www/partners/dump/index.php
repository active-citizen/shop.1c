<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы::Кабинет партнёра");
require("../group_access.php");
?>
<div class="partners-main">
    <h1>Перенос данных</h1>
    <? include("../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:data.dump","",array(
        "DUMP_FOLDER"=>$_SERVER["DOCUMENT_ROOT"],"/dump",
        "CACHE_TIME"=>1
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
