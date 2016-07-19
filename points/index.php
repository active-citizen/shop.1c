<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои баллы");
?>

<?$APPLICATION->IncludeComponent(
    "ag:points", 
    "",
    array(
        "ALL_TITLE"         =>  "Все начисления и списания",
        "SELF_FOLDER"       =>  "/points/",
        "ALL_FOLDER"        =>  "all",
        "DEBIT_FOLDER"      =>  "debit",
        "CREDIT_FOLDER"     =>  "credit",
        "RECORDS_ON_PAGE"   =>  30
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>