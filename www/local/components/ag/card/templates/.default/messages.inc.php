<? if($stopMonLimit):?>
  <? include("messages/monlimit.inc.php")?>
<? elseif(
    $arResult["ACCOUNT"]["CURRENT_BUDGET"] < 
    $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
    &&
    CUser::IsAuthorized()
): ?>
    <? include("messages/pointslimit.inc.php");?>
<? elseif(
    !CUser::IsAuthorized()
): ?>
    <? include("messages/authlimit.inc.php");?>
<? elseif($stopDailyLimit):?>
    <? include("messages/daylylimit.inc.php");?>
<? elseif(
    (
        
        !trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        &&
        !
        intval($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
            [0]["VALUE"])
        
    )
    ||
    (
        !trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        &&
        intval(
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
            [0]["VALUE"]
        )
        &&
        (
            $arResult["USER_INFO"]["UF_USER_ALL_POINTS"]
            <
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
                [0]["VALUE"]
        )
        
   )
):?>
    <? include("messages/aglimit.inc.php");?>
<? elseif($arResult["AUCTION"] 
&& $arResult["AUCTION"]["IS_FINISHED"]):?>
    <? include("messages/auction_lastlimit.inc.php");?>
<? elseif($arResult["AUCTION"] &&
!$arResult["AUCTION"]["IS_CURRENT"] &&
!$arResult["AUCTION"]["IS_FINISHED"]):?>
    <? include("messages/auction_futurelimit.inc.php");?>
<? elseif($arResult["AUCTION"] && $arResult["AUCTION"]["IS_CURRENT"]):?>
    <? include("messages/auction_nowlimit.inc.php");?>
<? endif ?>


