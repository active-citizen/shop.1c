<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
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
?>
<?

?>
<? if(
    isset($arResult["CATALOG_ITEM"]["USERCATS"])
    &&
    count($arResult["CATALOG_ITEM"]["USERCATS"])
    &&
    !array_intersect($arResult["CATALOG_ITEM"]["USERCATS"],$arResult["USERCATS_IDS"])
):?>
    <? include("errors/forbidden.inc.php");?>
<? elseif(
    $arResult["CATALOG_ITEM"]["ACTIVE"]=='N' 
    ||
    $arResult["HIDE_ON_DATE"]
):?>
    <? include("messages/nonactive.inc.php");?>
<? elseif(
    !$arResult["TotalAmount"] && 
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["HIDE_IF_ABSENT"][0]["VALUE_ENUM"]=='да'
):?>
    <? include("errors/nonstore.inc.php");?>
<? elseif(
    isset($arResult["OFFERS"][0]) 
    && $arResult["CATALOG_ITEM"]["ACTIVE"]=='Y'
):?>
    <div class="ag-shop-card">
        <? require("messages.inc.php");?>           

        <div class="grid grid--bleed">
            <div class="grid__col-12 grid__col-md-shrink">
                <div class="ag-shop-card__left-column">
                    <? require("blocks/images.inc.php");?>
                    <? require("blocks/header.inc.php");?>
                </div>
            </div>
            <div class="grid__col-12 grid__col-md-auto">
                <div class="ag-shop-card__right-column">
                    <div class="ag-shop-card__container">
                        <div class="ag-shop-card__header">
                            <h2 class="ag-shop-card__header-title">
                                <?= $arResult["CATALOG_ITEM"]["NAME"]?>
                            </h2>
                
                            <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]
                                ["PERFOMANCE_DATE"][0]["VALUE"]):?>
                                <? require("blocks/actiondate.inc.php");?>
                            <? endif ?>
            
                            <?= $useBeforeHtml; ?>
                        </div>
                        <? require("blocks/troyka.inc.php");?>
                        <? require("blocks/props.inc.php");?>
                        <? require("blocks/stores.inc.php");?>
                        <? require("blocks/counter.inc.php");?>

                    </div>
                </div>
            </div>
        </div>

        <div class="ag-shop-card__container">
            <div class="ag-shop-card__field ag-shop-card__field--no-gaps">
                <? require("blocks/description.inc.php");?>
                <? require("blocks/auctionwinners.inc.php");?>
        </div>

        <? require("blocks/expires.inc.php");?>
        <? require("blocks/orderbutton.inc.php");?>

        <div class="ag-shop-card__additional-info"><?/* </div> */?>
        </div>
        </div>
    </div>

    <? require("blocks/orderwin.inc.php");?>
    <? require("blocks/auctionwin.inc.php");?>
    <? require("blocks/troykawin.inc.php");?>

<? else: ?>
    <h3>Нет доступных предложений</h3>
<? endif ?>

<? require("blocks/js.inc.php");?>
