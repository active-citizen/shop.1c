<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пользователи личного кабинета");
?>
<div class="partners-main">
    <? include("../../menu.php"); ?>
    <form action="/partners/users/add/">
        <input type="hidden" name="backurl" value="<?= $_SERVER["REQUEST_URI"]?>">
        <input type="submit" name="add" class="btn btn-success" value="Добавить пользователя">
    </form>
    <?$APPLICATION->IncludeComponent("ag:partners.users.list","",array(
        "CACHE_TIME"    =>  1,
        "PAGE_NUM"      =>  isset($_REQUEST["PAGEN_1"])?$_REQUEST["PAGEN_1"]:1,
        "RECORDS_ON_PAGE"=> 20
    ),false);?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
