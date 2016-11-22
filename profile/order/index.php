<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");

include("../menu.php");
?>
        <div class="ag-shop-content">
          <div class="ag-shop-content__limited-container">
<?$APPLICATION->IncludeComponent("ag:orders", "", array(
    "SEF_MODE"  => "Y",
    "USER_ID"   =>  $USER->GetId(),
    "RECORDS_ON_PAGE"   => 2,
    "SEF_FOLDER" => "/profile/order/",
    "ORDERS_PER_PAGE" => "10",
    "PATH_TO_PAYMENT" => "/profile/order/payment/",
    "SET_TITLE" => "Y",
    "SAVE_IN_SESSION" => "N",
    "NAV_TEMPLATE" => "arrows",
    "SEF_URL_TEMPLATES" => array(
        "list" => "index.php",
        "detail" => "detail/#ID#/",
        "cancel" => "cancel/#ID#/",
    ),
    "SHOW_ACCOUNT_NUMBER" => "Y"
    ),
    false
);?>
          </div>
        </div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
