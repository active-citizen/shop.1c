<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Кабинет партнёра</h1>
    <? include("../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:partners.orders.list","",array(
        "CACHE_TIME"=>1
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
