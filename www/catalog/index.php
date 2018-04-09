<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    CModule::IncludeModule("iblock");
    $APPLICATION->SetTitle(
        "Магазин поощрений &laquo;Активный Гражданин&raquo;"
    );
    
?>

<? require("desktop.filter.params.php");?>
<? if(0 && !$USER->IsAuthorized()):?>
<? elseif(IS_MOBILE || IS_PHONE):?>
<? require("index.mobile.php")?>
<? else: ?>
    <? include_once("banners.inc.php");?>
    <? include_once("filter.inc.php");?>
        
          <!-- Catalog {{{-->
  <div class="ag-shop-catalog">
    
    <? include_once("container.inc.php");?>

  </div>
<? endif ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
