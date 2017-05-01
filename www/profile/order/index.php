<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");

include("../menu.php");
?><div class="ag-shop-content">
	<div class="ag-shop-content__limited-container">
		 <?$APPLICATION->IncludeComponent(
	"ag:orders",
	"",
	Array(
		"CATALOG_IBLOCK_ID" => CATALOG_IB_ID,
		"NAV_TEMPLATE" => "arrows",
		"OFFER_IBLOCK_ID" => OFFER_IB_ID,
		"ORDERS_PER_PAGE" => "10",
		"PATH_TO_PAYMENT" => "/profile/order/payment/",
		"RECORDS_ON_PAGE" => 10,
		"SAVE_IN_SESSION" => "N",
		"SEF_FOLDER" => "/profile/order/",
		"SEF_MODE" => "Y",
		"SEF_URL_TEMPLATES" => array("list"=>"index.php","detail"=>"detail/#ID#/","cancel"=>"cancel/#ID#/",),
		"SET_TITLE" => "Y",
		"SHOW_ACCOUNT_NUMBER" => "Y",
		"USER_ID" => $USER->GetId()
	)
);?>
	</div>
</div><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
