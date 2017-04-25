<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Часто задаваемые вопросы");
include("../menu.php");
?>
        <div class="ag-shop-content">
          <div class="ag-shop-content__limited-container">
            <?$APPLICATION->IncludeComponent("ag:faq","",array(
                "CACHE_TIME"=>COMMON_CACHE_TIME
            ),false);?> 
          </div>
        </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
