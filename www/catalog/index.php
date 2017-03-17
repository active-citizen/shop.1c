<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    CModule::IncludeModule("iblock");
?>

        <div class="ag-shop-content">

          <? include_once("banners.inc.php");?>
          <? include_once("filter.inc.php");?>

          
          <!-- Catalog {{{-->
          <div class="ag-shop-catalog">
            <!-- Для сортировки/фильтра-->
            <? include_once("sorting.inc.php");?>
            
            <? include_once("container.inc.php");?>
    
          </div>
        </div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
