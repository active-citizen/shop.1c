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
$arStores = [
    [
        "ID"=>0,
        "VALUE"=>"Цена(баллы)"
    ]
];
$arTable = array();

while($arStoreProduct = $resStoreProduct->GetNext()){
    $arProductInfo =getProductInfo(
            $arStoreProduct["PRODUCT_ID"]
        ); 
    if(!$arProductInfo)continue;
    if(!isset($arProducts[$arStoreProduct["PRODUCT_ID"]]))
        $arProducts[$arStoreProduct["PRODUCT_ID"]] = $arProductInfo;

    if(!isset($arStores[$arStoreProduct["STORE_ID"]]))
        $arStores[$arStoreProduct["STORE_ID"]] = array(
            "ID"    =>  $arStoreProduct["STORE_ID"],
            "VALUE" =>  $arStoreProduct["STORE_NAME"]." ID=[".
                $arStoreProduct["STORE_ID"]
            ."]"
        );
   if(!isset($arTable[$arStoreProduct["PRODUCT_ID"]]))
        $arTable[$arStoreProduct["PRODUCT_ID"]] =
            [0=>$arProductInfo["PRODUCT"]["PROPERTY_MINIMUM_PRICE_VALUE"]];
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


//echo "<pre><!-- ";
//print_r($arResult);
//echo " --></pre>";

// Чистим результат от неактивных товаров
/*
foreach($arResult["ROWS"] as $nRowId=>$arRow)
    if($arRow["PRODUCT"]["ACTIVE"]!='Y'){
        unset($arResult["ROWS"][$nRowId]);
        unset($arResult["CELLS"][$nRowId]);
    }
*/

// Вычищаем товары без остатка
foreach($arResult["CELLS"] as $nRowId=>$arRow){
    $nCount = 0;
    foreach($arRow as $nColId=>$nCell)
        $nCount += intval($nCell);
    if(
        (
            $arProducts[$nRowId]["PRODUCT"]["PROPERTY_HIDE_IF_ABSENT_VALUE"]
            &&
            $nCount<=0
        )
        ||
        (
            $arProducts[$nRowId]["PRODUCT"]["ACTIVE"]!='Y'
        )
    ){
        unset($arResult["ROWS"][$nRowId]);
        unset($arResult["CELLS"][$nRowId]);
        continue;
    }
    if($arProducts[$nRowId]["PRODUCT"]["PROPERTY_HIDE_DATE_VALUE"]){
        $nTimeStamp = MakeTimeStamp(
            $arProducts[$nRowId]["PRODUCT"]["PROPERTY_HIDE_DATE_VALUE"],
            "DD.MM.YYYY"
        );
        if($nTimeStamp<=time()){
            unset($arResult["ROWS"][$nRowId]);
            unset($arResult["CELLS"][$nRowId]);
            continue;
        }
    }


}


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
    if(!intval($arResult["OFFER"]["PROPERTY_CML2_LINK_VALUE"]))return false;
    $arResult["PRODUCT"] = 
        $arProduct = CIBlockElement::GetList(
            array(),
            array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$arResult["OFFER"]["PROPERTY_CML2_LINK_VALUE"]
            ),
            false,
            array("nTopCount"=>1),
            array(
                "NAME","ID","IBLOCK_SECTION_ID","CODE","ACTIVE",
                "PROPERTY_HIDE_IF_ABSENT","PROPERTY_HIDE_DATE",
                "PROPERTY_MINIMUM_PRICE"
            )
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
