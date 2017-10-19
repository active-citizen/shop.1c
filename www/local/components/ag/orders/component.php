<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");


$RU = $_SERVER["REQUEST_URI"];
if(CUser::isAuthorized()):
    // Значения по умолчанию
    if(!isset($arParams["RECORDS_ON_PAGE"]))$arParams["RECORDS_ON_PAGE"] = 10;
    if(!isset($arParams["SHOW_TOP_PAGINATION"]))$arParams["SHOW_TOP_PAGINATION"] = 1;
    if(!isset($arParams["SHOW_BOTTOM_PAGINATION"]))$arParams["SHOW_BOTTOM_PAGINATION"] = 1;
    if(!isset($arParams["PAGE_BLOCK_SIZE"]))$arParams["PAGE_BLOCK_SIZE"] = 10;

    if(!isset($arParams["CATALOG_IBLOCK_ID"]))$arParams["CATALOG_IBLOCK_ID"] = 2;
    if(!isset($arParams["OFFER_IBLOCK_ID"]))$arParams["OFFER_IBLOCK_ID"] = 3;


    if(!isset($arParams["PAGE"]) && !isset($_GET["page"]))
        $arParams["PAGE"] = 1;
    else
        $arParams["PAGE"] = intval($_GET["page"]);

    if(!isset($arParams["TAB"]) && !isset($_GET["tab"]))
        $arParams["TAB"] = 'all';
    else
        $arParams["TAB"] = htmlspecialchars($_GET["tab"]);
        


    CModule::IncludeModule("sale");
    CModule::IncludeModule("price");
    CModule::IncludeModule("catalog");
    $arResult = array("ORDERS"=>array(),"STATUSES"=>array(),"PAGES"=>array());


    /////////////////////////// Справочник статусов ///////////////////////////////
    $resStatuses = CSaleStatus::GetList();
    while($arStatus = $resStatuses->GetNext())
        $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;


    $arOrder = array("DATE_INSERT"=>"DESC");
    $arFilter = array();
    $arFilter["USER_ID"] = $arParams["USER_ID"];

    switch($arParams["TAB"]){
        case 'all':
            //$arFilter["STATUS_ID"] = array();
        break;
        case 'use':
            $arFilter["STATUS_ID"] = array('N','AA','AB','AF');
        break;
        case 'unuse':
            $arFilter["STATUS_ID"] = array('F','AI','AG','AC');
        break;
    }

    $arNavStartParams = array(
        "iNumPage"  =>  $arParams["PAGE"],
        "nPageSize" =>  $arParams["RECORDS_ON_PAGE"]
    );

    $resOrders = CSaleOrder::GetList($arOrder, $arFilter, false, $arNavStartParams);

    // Не выводить предустановленные заказы админа. Он пугается
    if($arFilter["USER_ID"]!=1)
    while($arOrder = $resOrders->GetNext()){
        // Склад
        $arOrder["STORE_INFO"] = CCatalogStore::GetList(
            array(),
            array("ID"=>$arOrder["STORE_ID"]),
            false,
            array("nTopCount"=>1),
            array()
        )->GetNext();
        $arOrder["PROPERTIES"] = orderGetProperties($arOrder["ID"]);
        
        $order = array();
        $order = $arOrder;
        $order["DATE_SHORT"] = date_parse($arOrder["DATE_INSERT"]);
        $nDateTimestamp = mktime(
            $order["DATE_SHORT"]["hour"],
            $order["DATE_SHORT"]["minute"],
            $order["DATE_SHORT"]["second"],
            $order["DATE_SHORT"]["month"],
            $order["DATE_SHORT"]["day"],
            $order["DATE_SHORT"]["year"]
        );
        $order["DATE_SHORT"] = date("d.m.y",$nDateTimestamp);
        $order["DATE_MIDDLE"] = date("d.m.y H:i",$nDateTimestamp);
        
        $order["PRODUCTS"] = array();
        $resProduct = CSaleBasket::GetList(array(),array("ORDER_ID"=>$arOrder["ID"]));
        while($arProduct = $resProduct->GetNext()){
            
            $arOffer = CIblockElement::GetList(array(),array(
                "IBLOCK_ID"=>$arParams["OFFER_IBLOCK_ID"],"ID"=>$arProduct["PRODUCT_ID"]
            ),false,array("nTopCount"=>1),array("PROPERTY_CML2_LINK"))->GetNext();

            $arCatalog = CIblockElement::GetList(array(),array(
                "IBLOCK_ID"=>$arParams["CATALOG_IBLOCK_ID"],"ID"=>$arOffer["PROPERTY_CML2_LINK_VALUE"]
            ),false,array("nTopCount"=>1),array(
                "PROPERTY_DAYS_TO_EXPIRE",
                "PROPERTY_USE_BEFORE_DATE",
                "PROPERTY_SEND_CERT"
            ))->GetNext();
            

            // Картинка продукта
            /////////////////
            $arProp =
            CIBlockElement::GetProperty($arParams["OFFER_IBLOCK_ID"],$arProduct["PRODUCT_ID"],array(),array("CODE"=>"CML2_LINK"))->GetNext();
            $catalogElementId = $arProp["VALUE"];
            
            $arCatalogItem = CIBlockElement::GetList(array(),array("IBLOCK_ID"=>$arParams["CATALOG_IBLOCK_ID"],"ID"=>$catalogElementId))->GetNext();
            
            //$arProp = CIBlockElement::GetProperty($arParams["CATALOG_IBLOCK_ID"],$catalogElementId,array(),array("CODE"=>"MORE_PHOTO"))->GetNExt();
            /////////
            $arProp =
            CIBlockElement::GetProperty($arParams["OFFER_IBLOCK_ID"],$arProduct["PRODUCT_ID"],array(),array("CODE"=>"MORE_PHOTO"))->GetNext();

            $arProduct["PIC_PATH"] = CFile::GetPath($arProp["VALUE"]);
            $arProduct["CATALOG_URL"] = $arCatalogItem["DETAIL_PAGE_URL"];
            
            // Возможность отмены
            $arProp = CIBlockElement::GetProperty($arParams["CATALOG_IBLOCK_ID"],$catalogElementId,array(),array("CODE"=>"CANCEL_ABILITY"))->GetNExt();
            $arProduct["CANCEL_ABILITY"] = $arProp["VALUE_ENUM"];
            
            $order["PRODUCTS"][] = $arProduct;
        }
        $order["SEND_CERT"] = $arCatalog["PROPERTY_SEND_CERT_VALUE"];
        $order["EXPIRES"] = $arCatalog["PROPERTY_DAYS_TO_EXPIRE_VALUE"];
        $order["USE_BEFORE"] = $arCatalog["PROPERTY_USE_BEFORE_DATE"];
        
        $tmp_0  = date_parse($arOrder["DATE_INSERT"]);
        $tmp_1  = date_parse($order["USE_BEFORE"]);
        
        $order["EXPIRES_TS"] = 
            mktime($tmp_0["hour"],$tmp_0["minute"],$tmp_0["second"],$tmp_0["month"],$tmp_0["day"],$tmp_0["year"])
            +
            $order["EXPIRES"]*24*60*60;
        $order["USE_BEFORE_TS"] = mktime($tmp_1["hour"],$tmp_1["minute"],$tmp_1["second"],$tmp_1["month"],$tmp_1["day"],$tmp_1["year"]);

        $order["IN_WORK"] = 0;
        if($order["EXPIRES"] && $order["USE_BEFORE"] && $order["EXPIRES_TS"] < $order["USE_BEFORE_TS"]){
            $order["IN_WORK"] = ($order["EXPIRES_TS"] - time())/(24*60*60);
        }
        elseif($order["EXPIRES"] && $order["USE_BEFORE"] && $order["EXPIRES_TS"] > $order["USE_BEFORE_TS"]){
            $order["IN_WORK"] = ($order["USE_BEFORE_TS"] - time())/(24*60*60);
        }
        elseif($order["EXPIRES"] && !$order["USE_BEFORE"]){
            $order["IN_WORK"] = ($order["EXPIRES_TS"] - time())/(24*60*60);
        }
        elseif(!$order["USE_BEFORE"] && $order["USE_BEFORE"]){
            $order["IN_WORK"] = ($order["USE_BEFORE_TS"] - time())/(24*60*60);
        }
        
        $arResult["ORDERS"][] = $order;
    }
    $res = CSaleOrder::GetList(array(),$arFilter,false);
    $total_pages = $res->SelectedRowsCount();
     
    $arResult["PAGES"] = get_pages_list(
        $total_pages,
        ($arParams["PAGE"]-1)*$arParams["RECORDS_ON_PAGE"],
        $arParams["RECORDS_ON_PAGE"],
        $arParams["PAGE_BLOCK_SIZE"]
    );
     
endif; 
$this->IncludeComponentTemplate();


/**
    Формирование массива страниц
    возвращает массив страниц вида
*/
function get_pages_list(
    $total,             //!< общее число записей
    $offset=0,          //!< номер рекущей страницы(начиная с 1)
    $perpage=10,        //!< число записей на страницу
    $blocksize = 10     //!< размер блока сраниц
){
    // Временно подкостылим
    $perpage=20;
    $blocksize = 100;
    // end: временно подкостылим
    if(!intval($perpage))$perpage = 10;
    
    $page = floor($offset/$perpage)+1;
    $page = intval($page) && $page>0?$page:1;
    $total = intval($total) && $total>0?$total:1;
    $perpage = intval($perpage) && $perpage>0?$perpage:10;
    $blocksize = intval($blocksize) && $blocksize>0?$blocksize:10;
    
    // Номер блока страниц
    $blocknum = floor(($page-1)/$blocksize + 1);
    // Определение общего количества страниц
    $total_pages = floor(($total-1)/$perpage + 1);
    // Определение общего количества блоков
    $total_blocks = floor(($total_pages-1)/$blocksize + 1);
    
    $result = array();
    if($blocknum>1){
        $result[0] = '1';
        $result[($blocknum-2)*$blocksize+($blocksize-1)] = '..';
    }
    for($i=($blocknum-1)*$blocksize+1;$i<=$blocknum*$blocksize && $i<=$total_pages;$i++){
        $result[($i-1)] = $i;
    }
    if($blocknum*$blocksize<$total_pages)$result[($blocknum*$blocksize)] = '..';
    if($blocknum*$blocksize<$total_pages)$result[($total_pages-1)] = $total_pages;
    
    
    return $result;
    
}
