<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Настройки</h1>
    <? include("../menu.php"); ?>
    <h2>Тройка</h2>
    <?$APPLICATION->IncludeComponent("ag:settings","",array(
        "CODE"  =>  "TROYKA",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 
</div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
