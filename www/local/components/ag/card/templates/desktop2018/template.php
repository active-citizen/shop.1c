<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    echo "<!-- ";
    print_r($arResult);
    echo " -->";
    ///////////////////////////////////////////////////////////////////////
    $this->createFrame()->begin("Загрузка");
    //$arResult["USER_INFO"]["UF_USER_AG_STATUS"] = 'Активный гражданин';
    $stopMonLimit = 0;
    $stopDailyLimit = 0;
    $noAG = false;
    if(
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"] && (
        $arResult["MON_ORDERS"]
            >=
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"]
    ))$stopMonLimit =
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"];
    if(
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAILY_LIMIT"][0]["VALUE"] && (
        $arResult["DAILY_ORDERS"]
            >=
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAILY_LIMIT"][0]["VALUE"]
    ))$stopDailyLimit =
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAILY_LIMIT"][0]["VALUE"];
    foreach($arResult["CATALOG_ITEM"]["USERCATS"] as $k=>$v)
        if(!trim($v))unset($arResult["CATALOG_ITEM"]["USERCATS"][$k]);
    ///////////////////////////////////////////////////////////////////////
    if(!$arResult["INFOTECH_ACTIVE"])$stopDailyLimit = 1;
?>

<div class="container-fluide">
  <div class="row">
    <? include("blocks/errors.inc.php"); ?>
    <? if(!$bFatalError):?>
        <?$arPics = $arResult["ALL_PICS"];?>
    
        <? include("blocks/leftbar.inc.php"); ?>
        <? include("blocks/mainpic.inc.php"); ?>
        <? include("blocks/desc.inc.php"); ?>
    <? endif ?>
  </div>


<? include("blocks/accord.inc.php");?>
<? include("blocks/recomends.inc.php");?>

<? include("blocks/modal.inc.php")?>

