<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Часто задаваемые вопросы");
include("../menu.php");
?>
        <div class="ag-shop-content">
          <div class="ag-shop-content__limited-container">
            <div class="ag-shop-rules">
              <div class="ag-shop-rules__content">
                <?$APPLICATION->IncludeComponent("ag:articles","",array(
                    "ID"=>1
                ),false);?> 
              </div>
            </div>
          </div>
        </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
