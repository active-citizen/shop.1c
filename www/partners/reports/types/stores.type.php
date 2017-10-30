<?
/**
    Формирование данных по складским остаткам разных товаров
*/

CModule::IncludeModule("catalog");

$arFilter = array();
//$arFilter[">AMOUNT"] = 0;


$resStoreProduct = CCatalogStoreProduct::GetList(
    array(),
    $arFilter
);

$arProducts = array();
$arStores = array();
$arTable = array();

while($arStoreProduct = $resStoreProduct->GetNext()){
    if(!isset($arProducts[$arStoreProduct["PRODUCT_ID"]]))
        $arProducts[$arStoreProduct["PRODUCT_ID"]] = getProductInfo(
            $arStoreProduct["PRODUCT_ID"]
        );
    if(!isset($arStores[$arStoreProduct["STORE_ID"]]))
        $arStores[$arStoreProduct["STORE_ID"]] = array(
            "ID"    =>  $arStoreProduct["STORE_ID"],
            "VALUE" =>  $arStoreProduct["STORE_NAME"]." ID=[".
                $arStoreProduct["STORE_ID"]
            ."]"
        );
   if(!isset($arTable[$arStoreProduct["PRODUCT_ID"]]))
        $arTable[$arStoreProduct["PRODUCT_ID"]] = array();
   if(!isset($arTable[$arStoreProduct["PRODUCT_ID"]][$arStoreProduct["STORE_ID"]]))
        $arTable[$arStoreProduct["PRODUCT_ID"]][$arStoreProduct["STORE_ID"]] = 0;

    $arTable[$arStoreProduct["PRODUCT_ID"]][$arStoreProduct["STORE_ID"]] +=
        $arStoreProduct["AMOUNT"];

}

// Результат для рисования таблицы
$arResult = array(
    "ROWS"=>$arProducts,
    "COLS"=>$arStores,
    "CELLS"=>$arTable
);

// Чистим результат от неактивных товаров
/*
foreach($arResult["ROWS"] as $nRowId=>$arRow)
    if($arRow["PRODUCT"]["ACTIVE"]!='Y'){
        unset($arResult["ROWS"][$nRowId]);
        unset($arResult["CELLS"][$nRowId]);
    }
*/

// Вычисляем товары без остатка
foreach($arResult["CELLS"] as $nRowId=>$arRow){
    $nCount = 0;
    foreach($arRow as $nColId=>$nCell)
        $nCount += intval($nCell);
    if($nCount>0)continue;
    if(!$arResult["ROWS"][$nRowId]["CLASS"])
        $arResult["ROWS"][$nRowId]["CLASS"] = 'absent';
}


//echo "<pre>";
//print_r($arResult);
//echo "</pre>";



uasort($arResult["ROWS"],"storagesSortFunc");


function storagesSortFunc($a,$b){
    return $a["CLASS"]>$b["CLASS"];
}

/**
    Получение информации от товаре по ID его предложения
*/
function getProductInfo($nOfferId){
    $arResult = array();
    $arResult["OFFER"] = 
        $arOffer = CIBlockElement::GetList(
            array(),
            array(
                "IBLOCK_ID" =>  OFFER_IB_ID,
                "ID"        =>  $nOfferId,
            ),
            false,
            array("nTopCount"=>1),
            array("NAME","ID","PROPERTY_CML2_LINK")
        )->GetNext();    
    $arResult["PRODUCT"] = 
        $arProduct = CIBlockElement::GetList(
            array(),
            array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$arResult["OFFER"]["PROPERTY_CML2_LINK_VALUE"]
            ),
            false,
            array("nTopCount"=>1),
            array("NAME","ID","IBLOCK_SECTION_ID","CODE","ACTIVE")
        )->GetNext();
    $arResult["SECTION"] = 
        $arSection = CIBlockSECTION::GetList(
            array(),
            array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$arResult["PRODUCT"]["IBLOCK_SECTION_ID"]
            ),
            false,
            array("NAME","ID","CODE"),
            array("nTopCount"=>1)
        )->GetNext();
    $arResult["VALUE"] = $arResult["PRODUCT"]["NAME"]." [ID=".$nOfferId."]";
    $arResult["URL"] = '/catalog/'.$arSection["CODE"].'/'.
        $arProduct["CODE"]."/";
    if( $arResult["PRODUCT"]["ACTIVE"]!='Y')
        $arResult["CLASS"] = "inactive";
    return $arResult;  
}
