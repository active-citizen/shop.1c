<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('catalog');


$sInFile = realpath(dirname(__FILE__))."/print_tmpl/act.xlsx";

require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/phpexcel/PHPExcel.php");
$oExcel = PHPExcel_IOFactory::load($sInFile);


$arParams["ORDER_ID"] = 
    isset($_REQUEST["order"]) && intval($_REQUEST["order"])
    ?
    intval($_REQUEST["order"])
    :
    0;

$arResult["USER"] = CUser::GetList(($by="personal_country"), ($order="desc"),array(
    "ID"=>CUSer::GetID()
))->Fetch();

$arResult["ORDER"] = CSaleOrder::GetList(
    array(),array("ID"=>$arParams["ORDER_ID"]),
    false,array("nTopCount"=>1),
    array(
    )
)->Fetch();

if(!$arResult["ORDER"]){
    echo "Order is not exists";
    die;
}

$arResult["SALER"] = CUser::GetList(($by="personal_country"), ($order="desc"),array(
    "ID"=>$arResult["ORDER"]["USER_ID"]
))->Fetch();

// Склад
$arResult["ORDER"]["STORE_INFO"] = CCatalogStore::GetList(
    array(),
    array("ID"=>$arResult["ORDER"]["STORE_ID"]),
    false,array("nTopCount"=>1)
)->Fetch();


//echo "<pre>";
//print_r($arResult);
//die;

$arResult["STATUSES"] = array();
$resStatuses = CSaleStatus::GetList();
while($arStatus = $resStatuses->Fetch())
    $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;
    


$resBasket = CSaleBasket::GetList(
    array(),
    array("ORDER_ID"=>$arResult["ORDER"]["ID"]),
    false,
    array("nTopCount"=>1)
);

$arResult["ORDER"]["BASKET"] = array();

while($arBasket = $resBasket->Fetch()){
    $arOffer = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_ID"=>OFFER_IB_ID,
            "ID"=>$arBasket["PRODUCT_ID"]
        ),
        false,
        array("nTopCount"=>1),
        array("PROPERTY_CML2_LINK")
    )->Fetch();

    $arProduct = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_ID"=>CATALOG_IB_ID,
            "ID"=>$arOffer["PROPERTY_CML2_LINK_VALUE"]
        ),
        false,
        array("nTopCount"=>1),
        array(
            "PROPERTY_SEND_CERT","ID","NAME","CODE","PREVIEW_PICTURE",
            "PROPERTY_MINIMUM_PRICE","IBLOCK_SECTION_ID","PROPERTY_QUANT"
        )
    //  array()
    )->Fetch();

    $arSection = CIBlockSection::GetList(
        array(),
        array("ID"=>$arProduct["IBLOCK_SECTION_ID"]),
        false,
        array(),
        array("nTopCount"=>1)
    )->Fetch();


    $arProduct["IMAGE"] = CFile::GetPath(
        $arProduct["PREVIEW_PICTURE"]
    );
    $arResult["ORDER"]["BASKET"][] = array(
        "BASKET_ITEM"   =>  $arBasket,
        "PRODUCT"       =>  $arProduct,
        "SECTION"       =>  $arSection
    );
}

$oExcel->getActiveSheet()->setCellValue('H3', get_date(date("d.m.Y")));
$oExcel->getActiveSheet()->setCellValue('C5',
    $arResult["ORDER"]["STORE_INFO"]["TITLE"]." ".
    $arResult["ORDER"]["STORE_INFO"]["ADDRESS"]
);
$oExcel->getActiveSheet()->setCellValue('C7', "«МФЦ07/15-132»");
$oExcel->getActiveSheet()->setCellValue('C8', 
    $arResult["USER"]["LAST_NAME"]
    ." ".$arResult["USER"]["NAME"]
    .", действующий на основании: «№01-1-363/15 от 17.07.2015»"); 
$oExcel->getActiveSheet()->setCellValue('A12', 
    "по заказу № "
    .$arResult["ORDER"]["ADDITIONAL_INFO"]
    ."  от ".get_date($arResult["ORDER"]["DATE_INSERT"])
);
$oExcel->getActiveSheet()->setCellValue('B14', 
    $arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["NAME"]
);
$oExcel->getActiveSheet()->setCellValue('D14', 
    intval($arResult["ORDER"]["ADDITIONAL_INFO"])
    ?
    "AКТА-".$arResult["ORDER"]["ADDITIONAL_INFO"]
    :
    "AКТ".$arResult["ORDER"]["ADDITIONAL_INFO"]
);
$oExcel->getActiveSheet()->setCellValue('E14', 
    $arResult["ORDER"]["BASKET"][0]["PRODUCT"]["PROPERTY_QUANT_VALUE"]
);
$oExcel->getActiveSheet()->setCellValue('F14', 
    $arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["QUANTITY"]
);
$oExcel->getActiveSheet()->setCellValue('G14', 
    intval($arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["PRICE"])
);
$oExcel->getActiveSheet()->setCellValue('F15', 
    $arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["QUANTITY"]
);
$oExcel->getActiveSheet()->setCellValue('G15', 
    intval($arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["PRICE"])
);
$oExcel->getActiveSheet()->setCellValue('C18', 
    $arResult["USER"]["LAST_NAME"]
    ." ".$arResult["USER"]["NAME"]
); 
$oExcel->getActiveSheet()->setCellValue('C23', 
    $arResult["SALER"]["LAST_NAME"]
    ." ".$arResult["SALER"]["NAME"]
); 
$oExcel->getActiveSheet()->setCellValue('B25', 
    str_replace("u","8",$arResult["SALER"]["LOGIN"])
); 
$oExcel->getActiveSheet()->setCellValue('B26', 
    str_replace("u","8",$arResult["SALER"]["EMAIL"])
); 


//echo "<pre>";
//print_r($arResult);
//die;

$objWriter = PHPExcel_IOFactory::createWriter($oExcel,"Excel2007");
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-disposition: attachment;filename="'.
    $arResult["ORDER"]["ADDITIONAL_INFO"].'.xlsx"');
$objWriter->save('php://output');
die;



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
