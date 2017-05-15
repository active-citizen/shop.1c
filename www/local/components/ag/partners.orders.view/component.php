<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
$arParams["ORDER_ID"] = 
    isset($arParams["ORDER_ID"]) && intval($arParams["ORDER_ID"])
    ?
        intval($arParams["ORDER_ID"])
    :
    0;

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
// ID группы свойств
$arPropGroup = CSaleOrderPropsGroup::GetList(
    array(),
    $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
    false,
    array("nTopCount"=>1)
)->GetNext();
$nPropGroup = $arPropGroup["ID"];
// Получаем свойства заказа
$resPropValues = CSaleOrderProps::GetList(
    array("SORT" => "ASC"),
    array(
            "ORDER_ID"       => $arOrder["ID"],
            "PERSON_TYPE_ID" => 1,
            "PROPS_GROUP_ID" => $nPropGroup,
        ),
    false,
    false,
    array("ID","CODE","NAME")
);
$arOrder["ORDER"]["PROPERTIES"] = array();
$arResult["PROPERTIES"] = array();
while($arProp = $resPropValues->GetNext()){
    $arResult["PROPERTIES"][$arProp["CODE"]] = $arProp;
    $arResult["ORDER"]["PROPERTIES"][$arProp["CODE"]] = 
        CSaleOrderPropsValue::GetList(
            array(),
            $arFilterProp = array(
                "ORDER_ID"=>$arResult["ORDER"]["ID"],
                "ORDER_PROPS_ID"=>$arProp["ID"]
            )
        )->GetNext();
}

if(isset($_REQUEST["chansge_status"])){

    $objCSaleOrderPropsValue = new CSaleOrderPropsValue; 

    $arFilter = array(
        "ORDER_ID"      =>  $arResult["ORDER"]["ID"],
        "ORDER_PROPS_ID"=>  $arResult["PROPERTIES"]["CHANGE_REQUEST"]["ID"],
        "CODE"          =>  "CHANGE_REQUEST",
        "NAME"          =>  $arResult["PROPERTIES"]["CHANGE_REQUEST"]["NAME"],
    );

    if(
        $arExistPropValue = 
        CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
    ){
        $arFilter["VALUE"] = $_REQUEST["status_id"];
        if(!CSaleOrderPropsValue::Update(
            $arExistPropValue["ID"],
            $arFilter 
        ) && $bDebug){
            ShowMessage(array(
                "TYPE"=>"ERROR",
                "MESSAGE"=>"Ошибка добавления свойства ЗНИ"
            ));
            die;
        }
    }
    elseif($arPropValue["PROPERTY_VALUE"]){
        $arFilter["VALUE"] = $_REQUEST["status_id"];
        if(!$objCSaleOrderPropsValue->Add($arFilter) && $bDebug){
            ShowMessage(array(
                "TYPE"=>"ERROR",
                "MESSAGE"=>"Ошибка изменения свойства ЗНИ"
            ));
            die;
        }
    }
    orderZNI(
        $arResult["ORDER"]["ID"],
        $_REQUEST["status_id"],
        $arResult["ORDER"]["STATUS_ID"]
    );
}


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


$arResult["HISTORY_TYPES"] = array(
    "ORDER_STATUS_CHANGED"  =>  "Изменение статуса заказа",
    "ORDER_UPDATED"         =>  "Изменение заказа",
    "ORDER_ADDED"           =>  "Добавление заказа",
    "ORDER_ZNI"             =>  "Запрос на изменение статуса"
);



$arResult["ORDER"]["HISTORY"] = array();
$resHistory = CSaleOrderChange::GetList(
    array("ID"=>"DESC"),
    array(
        "ORDER_ID"=>$arResult["ORDER"]["ID"]
    ),
    false,
    array("nTopCount"=>10)

);
while($arHistoryItem = $resHistory->Fetch()){
    if(
        $arHistoryItem["ENTITY"] != "ORDER"
    )continue;
    $arHistoryItem["DATA"] = unserialize($arHistoryItem["DATA"]);
    if(!isset($arResult["HISTORY_TYPES"][$arHistoryItem["TYPE"]]))continue;
    // Имя пользователя
    $arHistoryItem["USER_INFO"] =
    CUser::GetById($arHistoryItem["USER_ID"])->Fetch();
    $arResult["ORDER"]["HISTORY"][] = $arHistoryItem;
}

$this->IncludeComponentTemplate();


