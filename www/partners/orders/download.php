<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Кабинет партнёра</h1>
    <? 
        require(realpath(dirname(__FILE__)."/../menu.php")); 
    ?>
    <?$APPLICATION->IncludeComponent("ag:partners.orders.download","",array(
        "CACHE_TIME"    =>  1
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
