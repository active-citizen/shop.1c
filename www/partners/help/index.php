<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Помощь::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Помощь</h1>
    <? include("../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:articles","",array(
        "CODE"  =>  "partners_instruction",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 
</div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
