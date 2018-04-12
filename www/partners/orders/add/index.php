<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы::Кабинет партнёра");
require("../../group_access.php");
?>
<div class="partners-main">
    <h1>Добавление заказа</h1>
    <? include("../../menu.php"); ?>
    <?$APPLICATION->IncludeComponent("ag:order.add","",[
    ],false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
