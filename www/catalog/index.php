<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    CModule::IncludeModule("iblock");
    $APPLICATION->SetTitle(
        "Магазин поощрений &laquo;Активный Гражданин&raquo;"
    );
    
?>
<? if(0 && !$USER->IsAuthorized()):?>
<? elseif(IS_MOBILE):?>

<? else: ?>

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
<? endif ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
