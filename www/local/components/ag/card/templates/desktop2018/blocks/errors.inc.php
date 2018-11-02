<? $bFatalError = false;?>
<? if(
    isset($arResult["CATALOG_ITEM"]["USERCATS"])
    &&
    count($arResult["CATALOG_ITEM"]["USERCATS"])
    &&
    !array_intersect($arResult["CATALOG_ITEM"]["USERCATS"],$arResult["USERCATS_IDS"])
):
$bFatalError = true;
?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Поощрение недоступно для заказа
        </p>
      </div>    
    </div>
<? elseif(
    $arResult["CATALOG_ITEM"]["ACTIVE"]=='N' 
    ||
    $arResult["HIDE_ON_DATE"]
):
$bFatalError = true;
?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Поощрение недоступно (снято с реализации)
        </p>
      </div>
    </div>
<? elseif(
    !$arResult["TotalAmount"] && 
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["HIDE_IF_ABSENT"][0]["VALUE_ENUM"]=='да'
):
$bFatalError = true;
?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Поощрение недоступно (исчерпание остатков)
        </p>
      </div>
    </div>
    <? include("errors/nonstore.inc.php");?>
<? elseif(
    isset($arResult["OFFERS"][0]) 
    && $arResult["CATALOG_ITEM"]["ACTIVE"]=='Y'
):?>
    <? require("messages.inc.php")?>
<? endif ?>




