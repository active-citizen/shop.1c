<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PAGE"] = 
    isset($_REQUEST["PAGEN_1"])
    &&
    intval($_REQUEST["PAGEN_1"])
    ?
    intval($_REQUEST["PAGEN_1"])
    :
    1;
$arParams["ON_PAGE"]=20;

// Список складов, доступных пользователю
$arUser = CUser::GetList(
    ($by="personal_country"), ($order="desc"),
    array("ID"=>CUser::GetId()),
    array(
        "SELECT"=>array(
            "UF_USER_ALL_POINTS",
            "UF_USER_STORAGE_ALL",
            "UF_USER_STORAGE_ID",
            "UF_USER_MAN_ALL",
            "UF_USER_MAN_ID"
        ),
        "NAV_PARAMS"=>array("nTopCount"=>1)
    )
    
)->getNext();

$arStoreFilter = array();
if(!$arUser["UF_USER_STORAGE_ALL"] && count($arUser["UF_USER_STORAGE_ID"]))
    $arStoreFilter["ID"] = $arUser["UF_USER_STORAGE_ID"];
elseif(!$arUser["UF_USER_STORAGE_ALL"] && !count($arUser["UF_USER_STORAGE_ID"]))
    $arStoreFilter["ID"] = 0;
    

$resStores = CCatalogStore::GetList(
    array("ID"=>"ASC"),
    $arStoreFilter,
    false,
    false,
    array("TITLE","ID")
);
    
$arResult["STORES"] = array();
$arParams["MY_STORES_IDS"] = array();
while($arStore = $resStores->GetNext()){
    $arParams["MY_STORES_IDS"][] = $arStore["ID"];
    $arResult["STORES"][$arStore["ID"]] = $arStore;
}

// Список доступных пользователю произвдителей
$arManFilter = array();
if(!$arUser["UF_USER_MAN_ALL"] && count($arUser["UF_USER_MAN_ID"]))
    $arManFilter["ID"] = $arUser["UF_USER_MAN_ID"];
elseif(!$arUser["UF_USER_MAN_ALL"] && !count($arUser["UF_USER_MAN_ID"]))
    $arManFilter["ID"] = 0;

$arManFilter["IBLOCK_ID"] = MANUFACTURER_IB_ID;


$resMans = CIBlockElement::GetList(
    array("ID"=>"ASC"),
    $arManFilter,
    false,
    false,
    array("NAME","ID")
);
$arResult["MANS"] = array();
$arParams["MY_MANS_IDS"] = array();
while($arMan = $resMans->GetNext()){
    $arParams["MY_MANS_IDS"][] = $arMan["ID"];
    $arResult["MANS"][$arMan["ID"]] = $arMan;
}

// Справочник статусов
$arResult["STATUSES"] = array();
$resStatuses = CSaleStatus::GetList(
    array("SORT"=>"ASC")
    ,array("!COLOR"=>"")
    ,false
    ,false
    ,array("ID","NAME","COLOR")
);
while($arStatus=$resStatuses->GetNext()){
    $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;
}

// Справочник категорий
$resSection = CIBlockSection::GetList(
    array(),
    array(
        "IBLOCK_ID" =>  CATALOG_IB_ID
    ),
    false,
    array("NAME","SECTION_PAGE_URL","ID")
);
$arResult["SECTIONS"] = array();
while($arSection=$resSection->GetNext()){
    $arResult["SECTIONS"][$arSection["ID"]] = $arSection;
}


$arParams["FILTER"] = array();

$arParams["FILTER"]["STORE"] = 
    isset($_REQUEST["filter_storage"])
    ?
    $_REQUEST["filter_storage"]
    :
    "";
$arParams["FILTER"]["CLOSE_DATE"] = 
    isset($_REQUEST["filter_closedate"])
    ?
    $_REQUEST["filter_closedate"]
    :
    "";


$arParams["FILTER"]["MAN"] = 
    isset($_REQUEST["filter_man"])
    ?
    $_REQUEST["filter_man"]
    :
    "";

$arParams["FILTER"]["NUM"] = 
    isset($_REQUEST["filter_num"])
    ?
    $_REQUEST["filter_num"]
    :
    "";
$arParams["FILTER"]["LAST_NAME"] = 
    isset($_REQUEST["filter_name"])
    ?
    $_REQUEST["filter_name"]
    :
    "";
$arParams["FILTER"]["PRODUCT"] = 
    isset($_REQUEST["filter_product"])
    ?
    $_REQUEST["filter_product"]
    :
    "";
$arParams["FILTER"]["SECTION"] = 
    isset($_REQUEST["filter_cat"])
    ?
    $_REQUEST["filter_cat"]
    :
    "";
$arParams["FILTER"]["STATUS"] = 
    isset($_REQUEST["filter_status"]) 
    ?
    $_REQUEST["filter_status"]
    :
    "";

$arParams["FILTER"]["ADDDATE"] = 
    isset($_REQUEST["filter_adddate"]) 
    ?
    $_REQUEST["filter_adddate"]
    :
    "";

$arParams["FILTER"]["EMAIL"] = 
    isset($_REQUEST["filter_email"]) 
    ?
    $_REQUEST["filter_email"]
    :
    "";

$arParams["FILTER"]["PHONE"] = 
    isset($_REQUEST["filter_phone"]) 
    ?
    $_REQUEST["filter_phone"]
    :
    "";

$arParams["SORT"] = array();
$arParams["SORT"]["NUM"] = 
    isset($_REQUEST["sort_num"]) 
    ?
    $_REQUEST["sort_num"]
    :
    "";
$arParams["SORT"]["LAST_NAME"] = 
    isset($_REQUEST["sort_name"]) 
    ?
    $_REQUEST["sort_name"]
    :
    "";
$arParams["SORT"]["STATUS"] = 
    isset($_REQUEST["sort_status"]) 
    ?
    $_REQUEST["sort_status"]
    :
    "";
$arParams["SORT"]["PRODUCT"] = 
    isset($_REQUEST["sort_product"]) 
    ?
    $_REQUEST["sort_product"]
    :
    "";
$arParams["SORT"]["CLOSE_DATE"] = 
    isset($_REQUEST["sort_closedate"]) 
    ?
    $_REQUEST["sort_closedate"]
    :
    "";

$$arParams["SORT"]["SECTION"] = 
    isset($_REQUEST["sort_cat"]) 
    ?
    $_REQUEST["sort_cat"]
    :
    "";
$arParams["SORT"]["ADDDATE"] = 
    isset($_REQUEST["sort_adddate"]) 
    ?
    $_REQUEST["sort_adddate"]
    :
    "";
$arParams["SORT"]["EMAIL"] = 
    isset($_REQUEST["sort_email"]) 
    ?
    $_REQUEST["sort_email"]
    :
    "";
$arParams["SORT"]["PHONE"] = 
    isset($_REQUEST["sort_phone"]) 
    ?
    $_REQUEST["sort_phone"]
    :
    "";



$arOrder = array(
);
$arFilter = array(
    //"%ADDITIONAL_INFO"=>"Б"
);

if($arParams["FILTER"]["NUM"])
    $arFilter["%ADDITIONAL_INFO"] = $arParams["FILTER"]["NUM"];

if($arParams["FILTER"]["LAST_NAME"])
    $arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"] = 
       $arParams["FILTER"]["LAST_NAME"];

if($arParams["FILTER"]["PRODUCT"])
    $arFilter["%PROPERTY_VAL_BY_CODE_PRODUCT_NAME"] = 
       $arParams["FILTER"]["PRODUCT"];

if($arParams["FILTER"]["SECTION"])
    $arFilter["PROPERTY_VAL_BY_CODE_SECTION_ID"] = 
       $arParams["FILTER"]["SECTION"];


if($arParams["FILTER"]["CLOSE_DATE"]){
    $tmp = explode(".",$arParams["FILTER"]["CLOSE_DATE"]);
    $t0 = date("Y-m-d",mktime(0,0,0,$tmp[1],$tmp[0],$tmp[2]));
    $arFilter["PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = $t0; 
}




if($arParams["FILTER"]["STATUS"])
    $arFilter["STATUS_ID"] = $arParams["FILTER"]["STATUS"];

if($arParams["FILTER"]["ADDDATE"]){
    $arFilter["><DATE_INSERT"] = array(
        $arParams["FILTER"]["ADDDATE"]." 00:00:00",
        $arParams["FILTER"]["ADDDATE"]." 23:59:59"
    );
}

if($arParams["FILTER"]["EMAIL"])
    $arFilter["%USER_EMAIL"] = $arParams["FILTER"]["EMAIL"];

if($arParams["FILTER"]["PHONE"])
    $arFilter["%USER_LOGIN"] = $arParams["FILTER"]["PHONE"];

if(
    $arParams["FILTER"]["STORE"] 
    && intval($arParams["FILTER"]["STORE"])
    && !$arUser["UF_USER_STORAGE_ALL"]
    && in_array($arParams["FILTER"]["STORE"],$arUser["UF_USER_STORAGE_ID"])
){
    $arFilter["STORE_ID"] = $arParams["FILTER"]["STORE"];
}
elseif(
    $arParams["FILTER"]["STORE"] 
    && intval($arParams["FILTER"]["STORE"])
    && !$arUser["UF_USER_STORAGE_ALL"]
    && !in_array($arParams["FILTER"]["STORE"],$arUser["UF_USER_STORAGE_ID"])
){
    $arFilter["STORE_ID"] = 0;
}
elseif(
    $arParams["FILTER"]["STORE"] 
    && !$arUser["UF_USER_STORAGE_ALL"]
    && $arParams["FILTER"]["STORE"] == 'all'
){
    $arFilter["STORE_ID"] = $arParams["MY_STORES_IDS"];
}
elseif(
    $arParams["FILTER"]["STORE"] 
    && intval($arParams["FILTER"]["STORE"])
    && $arUser["UF_USER_STORAGE_ALL"]
    && $arParams["FILTER"]["STORE"] != 'all'
){
    $arFilter["STORE_ID"] = $arParams["FILTER"]["STORE"];
}
 
if(
    $arParams["FILTER"]["MAN"] 
    && intval($arParams["FILTER"]["MAN"])
    && !$arUser["UF_USER_MAN_ALL"]
    && in_array($arParams["FILTER"]["MAN"],$arUser["UF_USER_MAN_ID"])
){
    $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] = $arParams["FILTER"]["MAN"];
}
elseif(
    $arParams["FILTER"]["MAN"] 
    && intval($arParams["FILTER"]["MAN"])
    && !$arUser["UF_USER_MAN_ALL"]
    && !in_array($arParams["FILTER"]["MAN"],$arUser["UF_USER_MAN_ID"])
){
     $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] = 0;
}
elseif(
    $arParams["FILTER"]["MAN"] 
    && !$arUser["UF_USER_MAN_ALL"]
    && $arParams["FILTER"]["MAN"] == 'all'
){
   $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] = $arParams["MY_MANS_IDS"];
}
elseif(
    $arParams["FILTER"]["MAN"] 
    && intval($arParams["FILTER"]["MAN"])
    && $arUser["UF_USER_MAN_ALL"]
    && $arParams["FILTER"]["MAN"] != 'all'
){
    $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] = $arParams["FILTER"]["MAN"];
}


$arResult["FILTER"] = $arParams["FILTER"];


if($arParams["SORT"]["SECTION"] && $arParams["SORT"]["SECTION"]=='▲')
    $arOrder["PROPERTY_VAL_BY_CODE_SECTION_NAME"] = 'DESC';
elseif($arParams["SORT"]["SECTION"] && $arParams["SORT"]["SECTION"]=='▼')
    $arOrder["PROPERTY_VAL_BY_CODE_SECTION_NAME"] = 'ASC';

if($arParams["SORT"]["CLOSE_DATE"] && $arParams["SORT"]["CLOSE_DATE"]=='▲')
    $arOrder["PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = 'DESC';
elseif($arParams["SORT"]["CLOSE_DATE"] && $arParams["SORT"]["CLOSE_DATE"]=='▼')
    $arOrder["PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = 'ASC';

if($arParams["SORT"]["NUM"] && $arParams["SORT"]["NUM"]=='▲')
    $arOrder["ADDITIONAL_INFO"] = 'DESC';
elseif($arParams["SORT"]["NUM"] && $arParams["SORT"]["NUM"]=='▼')
    $arOrder["ADDITIONAL_INFO"] = 'ASC';

if($arParams["SORT"]["LAST_NAME"] && $arParams["SORT"]["LAST_NAME"]=='▲')
    $arOrder["PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"] = 'DESC';
elseif($arParams["SORT"]["LAST_NAME"] && $arParams["SORT"]["LAST_NAME"]=='▼')
    $arOrder["PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"] = 'ASC';

if($arParams["SORT"]["PRODUCT"] && $arParams["SORT"]["PRODUCT"]=='▲')
    $arOrder["PROPERTY_VAL_BY_CODE_PRODUCT_NAME"] = 'DESC';
elseif($arParams["SORT"]["PRODUCT"] && $arParams["SORT"]["PRODUCT"]=='▼')
    $arOrder["PROPERTY_VAL_BY_CODE_PRODUCT_NAME"] = 'ASC';

if($arParams["SORT"]["STATUS"] && $arParams["SORT"]["STATUS"]=='▲')
    $arOrder["STATUS_ID"] = 'DESC';
elseif($arParams["SORT"]["STATUS"] && $arParams["SORT"]["STATUS"]=='▼')
    $arOrder["STATUS_ID"] = 'ASC';

if($arParams["SORT"]["ADDDATE"] && $arParams["SORT"]["ADDDATE"]=='▲')
    $arOrder["DATE_INSERT"] = 'DESC';
elseif($arParams["SORT"]["ADDDATE"] && $arParams["SORT"]["ADDDATE"]=='▼')
    $arOrder["DATE_INSERT"] = 'ASC';

if($arParams["SORT"]["EMAIL"] && $arParams["SORT"]["EMAIL"]=='▲')
    $arOrder["USER_EMAIL"] = 'DESC';
elseif($arParams["SORT"]["EMAIL"] && $arParams["SORT"]["EMAIL"]=='▼')
    $arOrder["USER_EMAIL"] = 'ASC';

if($arParams["SORT"]["PHONE"] && $arParams["SORT"]["PHONE"]=='▲')
    $arOrder["USER_LOGIN"] = 'DESC';
elseif($arParams["SORT"]["PHONE"] && $arParams["SORT"]["PHONE"]=='▼')
    $arOrder["USER_LOGIN"] = 'ASC';

//echo "<pre>";
//print_r($arOrder);
//print_r($arFilter);
//echo "</pre>";



$arSelect = array(
    "ID",
    "STATUS_ID",
    "ADDITIONAL_INFO",
    "USER_LAST_NAME",
    "USER_NAME",
    "DATE_INSERT",
    "USER_EMAIL",
    "USER_LOGIN",
    "STORE_ID",
);
//$arSelect = array();


$arResult["resOrders"] = CSaleOrder::GetList(
    $arOrder,
    $arFilter,
    false,
    array(
       "nPageSize"  =>  $arParams["ON_PAGE"],
       "iNumPage"   =>  $arParams["PAGE"]
    ),
    $arSelect
);

$arPropGroup = CSaleOrderPropsGroup::GetList(
    array(),
    $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
    false,
    array("nTopCount"=>1)
)->GetNext();
$nPropGroup = $arPropGroup["ID"];

$arResult["ORDERS"] = array();
while($arOrder = $arResult["resOrders"]->GetNext()){

    $resPropValues = CSaleOrderProps::GetList(
        array("SORT" => "ASC"),
        array(
                "ORDER_ID"       => $arOrder["ID"],
                "PERSON_TYPE_ID" => 1,
                "PROPS_GROUP_ID" => $nPropGroup,
            ),
        false,
        false,
        array("ID","CODE")
    );


    $arOrder["PROPERTIES"] = array();
    while($arProp = $resPropValues->GetNext()){
        
        $arOrder["PROPERTIES"][$arProp["CODE"]] = 
            CSaleOrderPropsValue::GetList(
                array(),
                $arFilterProp = array(
                    "ORDER_ID"=>$arOrder["ID"],
                    "ORDER_PROPS_ID"=>$arProp["ID"]
                )
            )->Fetch();
    }
   

    $tmp = explode(" ",$arOrder["DATE_INSERT"]);
    $arOrder["DATE_INSERT"] = $tmp[0];

    $arResult["ORDERS"][] = $arOrder;    
}

//echo "<pre>";
//print_r($arResult["ORDERS"]);
//echo "</pre>";
    
$this->IncludeComponentTemplate();


