<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Центры выдачи");

include("../menu.php")

?>
        <div class="ag-shop-content">
          <div class="ag-shop-rules">
            <div class="ag-shop-rules__content">
              <div class="ag-shop-content__limited-container">
                <mark><strong>Список центров госуслуг, в которых вы можете получить поощрение:</strong></mark>
              </div>
            </div>

                <?$APPLICATION->IncludeComponent("ag:stores","",Array(
                    "CACHE_TIME"=>COMMON_CACHE_TIME
                ),false);?> 

          </div>
        </div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
