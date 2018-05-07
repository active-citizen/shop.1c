<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if($_SERVER["REQUEST_URI"]=='/profile/'){
    LocalRedirect("/profile/order/");
    die;
}
$APPLICATION->SetTitle("Часто задаваемые вопросы");
include("menu.php");
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
