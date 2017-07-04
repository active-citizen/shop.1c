<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('catalog');

$sPrintType = $_REQUEST["print"];

if(!preg_match("/^[\d\w]+$/",$sPrintType)){
    echo "Неверный формат типа печатной формы";
    die;
}

$sInFile = realpath(dirname(__FILE__))."/print_tmpl/".$sPrintType.".xlsx";
if(!file_exists($sInFile)){
    echo "Файл шаблона печатной формы отсутствует";
    die;
}

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
            "PROPERTY_MINIMUM_PRICE","IBLOCK_SECTION_ID","PROPERTY_QUANT",
            "PROPERTY_BUH_NAME"
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

// Заполняем данные для печатной формы act
if($_REQUEST["print"]=='act'){
    $oExcel->getActiveSheet()->setCellValue('G3', get_date(date("d.m.Y")));
    $oExcel->getActiveSheet()->setCellValue('A20', get_date(date("d.m.Y")));
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
//        $arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["NAME"]
        $arProduct["PROPERTY_BUH_NAME_VALUE"]
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
    $oExcel->getActiveSheet()->getCell('B25')->setValueExplicit('1.1',
    PHPExcel_Cell_DataType::TYPE_STRING);
    $sPhone = str_replace("u7","  ",$arResult["SALER"]["LOGIN"]);
    $sPhone =
        ' 8 '.
        substr($sPhone,2,3)
        ." ".substr($sPhone,5,3)
        ." ".substr($sPhone,8,2)
        ." ".substr($sPhone,10);
    $oExcel->getActiveSheet()->setCellValue('B25',$sPhone); 
    $oExcel->getActiveSheet()->setCellValue('B26', 
        str_replace("u","u",$arResult["SALER"]["EMAIL"])
    ); 
}


if($_REQUEST["print"]=='cancel'){
    $oExcel->getActiveSheet()->setCellValue('C4', 
        $arResult["SALER"]["LAST_NAME"]
        ." ".$arResult["SALER"]["NAME"]
    ); 
    $oExcel->getActiveSheet()->setCellValue('C2',
        $arResult["ORDER"]["STORE_INFO"]["TITLE"]." ".
        $arResult["ORDER"]["STORE_INFO"]["ADDRESS"]
    );
    $oExcel->getActiveSheet()->setCellValue('B10',
        "Я, "
        .$arResult["SALER"]["LAST_NAME"]
        ." ".$arResult["SALER"]["NAME"]
        .", отказываюсь от получения заказа № "
        .$arResult["ORDER"]["ADDITIONAL_INFO"]
    );
    $oExcel->getActiveSheet()->setCellValue('D25', date("d.m.Y"));
    $oExcel->getActiveSheet()->setCellValue('J25', 
        $arResult["SALER"]["LAST_NAME"]
        ." ".$arResult["SALER"]["NAME"]
    ); 
    $oExcel->getActiveSheet()->setCellValue('J29', 
        $arResult["USER"]["LAST_NAME"]
        ." ".$arResult["USER"]["NAME"]
    ); 

}

//echo "<pre>";
//print_r($arResult);
//die;

$objWriter = PHPExcel_IOFactory::createWriter($oExcel,"Excel2007");
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-disposition: attachment;filename="'.$_REQUEST["print"]."_"
    .$arResult["ORDER"]["ADDITIONAL_INFO"].'.xlsx"');
$objWriter->save('php://output');
die;



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
