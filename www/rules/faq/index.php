<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Часто задаваемые вопросы");
include($_SERVER["DOCUMENT_ROOT"]."/rules/menu.php");
?>
        <div class="ag-shop-content">
          <div class="ag-shop-content__limited-container">
            <?$APPLICATION->IncludeComponent("ag:faq_sectioned","",array(
                "CACHE_TIME"=>COMMON_CACHE_TIME,
                "SECTION_ID"=>$_REQUEST["SECTION_ID"],
                "BASE_PATH" =>  "/rules/faq/"
            ),false);?> 
          </div>
        </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
