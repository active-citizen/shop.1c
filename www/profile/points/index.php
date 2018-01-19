<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои баллы");

    include(dirname(__FILE__)."/../menu.php");

?>
        <div class="ag-shop-content">
          <div class="ag-shop-content__limited-container">
            <!-- Profile {{{-->
<?$APPLICATION->IncludeComponent(
    "ag:points.ssag", 
    "",
    array(
        "ALL_TITLE"         =>  "Все начисления и списания",
        "SELF_FOLDER"       =>  "/profile/points/",
        "ALL_FOLDER"        =>  "all",
        "DEBIT_FOLDER"      =>  "debit",
        "CREDIT_FOLDER"     =>  "credit",
        "RECORDS_ON_PAGE"   =>  30
    )
);?>
            <!-- }}} Profile-->
          </div>
        </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
