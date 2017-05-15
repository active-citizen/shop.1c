<?php
require_once(
   $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"
);

/**
    Обновление свойств у заказа
*/
function orderPropertiesUpdate($nOrderId, $bDebug = false){
    
    $arOrder = CSaleOrder::GetList(
        array(),
        array(
            "ID"=>$nOrderId
        ),
        false,
        array(
            "nTopCount"=>1
        ),
        array("ID","USER_ID","USER_NAME","USER_LAST_NAME","DATE_INSERT") 
    )->Fetch();
    if(!$arOrder)return false;

    $arPropGroup = CSaleOrderPropsGroup::GetList(
        array(),
        $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
        false,
        array("nTopCount"=>1)
    )->GetNext();
    $nPropGroup = $arPropGroup["ID"];

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

    $arOrder["PROPERTIES"] = array();
    $nextFlag = false;
    while($arProp = $resPropValues->GetNext()){
        $arValue =  
            CSaleOrderPropsValue::GetList(
                array(),
                $arFilterProp = array(
                    "ORDER_ID"=>$arOrder["ID"],
                    "ORDER_PROPS_ID"=>$arProp["ID"]
                )
            )->GetNext();

        if(is_array($arValue["VALUE_ORIG"]))
            $arValue = '';
        else
            $arValue = $arValue["VALUE"];
        if($arValue){
            //$nextFlag = true;
            //break;
        }
        $arOrder["PROPERTIES"][$arProp["CODE"]] = array (
            "PROPERTY_SETTINGS" =>  $arProp,
            "PROPERTY_VALUE"    =>  $arValue
        );
    }
    if($nextFlag)continue;
    $arOrder["PROPERTIES"]["NAME_LAST_NAME"]["PROPERTY_VALUE"] = 
        $arOrder["USER_LAST_NAME"]." ".$arOrder["USER_NAME"];
    
    $tmp = explode(" ",$arOrder["DATE_INSERT"]);

    $arBasket = CSaleBasket::GetList(
        array(),
        array("ORDER_ID"=>$arOrder["ID"]),
        false,
        array("nTopCount"=>1)
    )->GetNext();

    $arOffer = CIBlockElement::GetList(
        array(),
        array(
            "ID"        =>  $arBasket["PRODUCT_ID"],
            "IBLOCK_ID" =>  OFFER_IB_ID
        ),
        false,
        array("nTopCount"=>1),            
        array("ID","PROPERTY_CML2_LINK")
    )->GetNext();
   

    $arCatalog = CIBlockElement::GetList(
        array(),
        array(
            "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"],
            "IBLOCK_ID" =>  CATALOG_IB_ID
        ),
        false,
        array("nTopCount"=>1),            
        array(
            "IBLOCK_SECTION_ID","NAME","DETAIL_PAGE_URL",
            "PROPERTY_DAYS_TO_EXPIRE","PROPERTY_MANUFACTURER_LINK"
        )
    )->GetNext();

    $arManufacturer = CIBlockElement::GetList(
        array(),
        array("ID"=>$arCatalog["PROPERTY_MANUFACTURER_LINK_VALUE"]),
        false,
        array("nTopCount"=>1),
        array("ID","NAME")
    )->GetNext();

    $arOrder["PROPERTIES"]["PRODUCT_URL"]["PROPERTY_VALUE"] = 
        $arCatalog["DETAIL_PAGE_URL"];
    $arOrder["PROPERTIES"]["PRODUCT_NAME"]["PROPERTY_VALUE"] = 
        $arCatalog["NAME"];
    $arOrder["PROPERTIES"]["SECTION_ID"]["PROPERTY_VALUE"] = 
        $arCatalog["IBLOCK_SECTION_ID"];
    $arOrder["PROPERTIES"]["MANUFACTURER_ID"]["PROPERTY_VALUE"] = 
        $arManufacturer["ID"];
    $arOrder["PROPERTIES"]["MANUFACTURER_NAME"]["PROPERTY_VALUE"] = 
        $arManufacturer["NAME"];


    $arCategory = CIBlockSection::GetList(
        array(),
        array(
            "ID"        =>  $arCatalog["IBLOCK_SECTION_ID"],
            "IBLOCK_ID" =>  CATALOG_IB_ID
        ),
        false,
        array("NAME","SECTION_PAGE_URL"),
        array("nTopCount"=>1)            
    )->GetNext();
    $arOrder["PROPERTIES"]["SECTION_NAME"]["PROPERTY_VALUE"] =  
        $arCategory["NAME"];
    $arOrder["PROPERTIES"]["SECTION_URL"]["PROPERTY_VALUE"] =  
        $arCategory["SECTION_PAGE_URL"];

    $arOrder["EXPIRES"] = $arCatalog["PROPERTY_DAYS_TO_EXPIRE_VALUE"];
    $arOrder["USE_BEFORE"] = $arCatalog["PROPERTY_USE_BEFORE_DATE"];
 
    $tmp_0  = date_parse($arOrder["DATE_INSERT"]);
    $tmp_1  = date_parse($arOrder["USE_BEFORE"]);

    $arOrder["EXPIRES_TS"] = 
        mktime(
            $tmp_0["hour"],$tmp_0["minute"],$tmp_0["second"],
            $tmp_0["month"],$tmp_0["day"],$tmp_0["year"]
        )
        +
        $arOrder["EXPIRES"]*24*60*60;
    $arOrder["USE_BEFORE_TS"] = 
        $tmp_1["errors"]
        ?
        mktime(0,0,0,12,12,3050)
        :
        mktime(
            $tmp_1["hour"],$tmp_1["minute"],$tmp_1["second"],
            $tmp_1["month"],$tmp_1["day"],$tmp_1["year"]
        );
    $arOrder["PROPERTIES"]["CLOSE_DATE"]["PROPERTY_VALUE"] =  
        $arOrder["EXPIRES_TS"]<$arOrder["USE_BEFORE_TS"]
        ?
        $arOrder["EXPIRES_TS"]
        :
        $arOrder["USE_BEFORE_TS"]
        ;
    $arOrder["PROPERTIES"]["CLOSE_DATE"]["PROPERTY_VALUE"] = 
        date("Y-m-d",intval($arOrder["PROPERTIES"]["CLOSE_DATE"]["PROPERTY_VALUE"]));

    $objCSaleOrderPropsValue = new CSaleOrderPropsValue;
    foreach($arOrder["PROPERTIES"] as $sPropName=>$arPropValue){
        $arFilter = array(
            "ORDER_ID"      =>  $arOrder["ID"],
            "ORDER_PROPS_ID"=>  $arPropValue["PROPERTY_SETTINGS"]["ID"],
            "CODE"          =>  $sPropName,
            "NAME"          =>  $arPropValue["PROPERTY_SETTINGS"]["NAME"]
        );

        if(
            $arExistPropValue = 
            CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
        ){
            if($bDebug){
            $arFilter["VALUE"] = $arPropValue["PROPERTY_VALUE"];
                echo "Edit\n";
                print_r($arFilter);
            }
            if(!CSaleOrderPropsValue::Update(
                $arExistPropValue["ID"],
                $arFilter 
            ) && $bDebug){
                echo "Eddik error\n";
                print_r($arExistPropValue);
                print_r($arPropValue);
                print_r($arOrder);
                die;
            }
        }
        elseif($arPropValue["PROPERTY_VALUE"]){
            $arFilter["VALUE"] = $arPropValue["PROPERTY_VALUE"];
            if($bDebug){
                echo "Add\n";
                print_r($arFilter);
            }
            if(!$objCSaleOrderPropsValue->Add($arFilter) && $bDebug){
                echo "Addik error\n";
                print_r($arFilter);
                print_r($arOrder);
                die;
            }
        }
        
    }
    return true;
}

/*
    Отправка запроса на изменение
*/
function orderSetZNI($nOrderId,$sStatusId,$sOldStatusId){

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
                "ORDER_ID"       => $nOrderId,
                "PERSON_TYPE_ID" => 1,
                "PROPS_GROUP_ID" => $nPropGroup,
            ),
        false,
        false,
        array("ID","CODE","NAME")
    );
    $PROPERTIES = array();
    while($arProp = $resPropValues->GetNext()){
        $PROPERTIES[$arProp["CODE"]] = $arProp;
    }


    $objCSaleOrderPropsValue = new CSaleOrderPropsValue; 

    $arFilter = array(
        "ORDER_ID"      =>  $nOrderId,
        "ORDER_PROPS_ID"=>  $PROPERTIES["CHANGE_REQUEST"]["ID"],
        "CODE"          =>  "CHANGE_REQUEST",
        "NAME"          =>  $PROPERTIES["CHANGE_REQUEST"]["NAME"],
    );

    if(
        $arExistPropValue = 
        CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
    ){
        $arFilter["VALUE"] = $sStatusId;
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
    elseif($sStatusId){
        $arFilter["VALUE"] = $sStatusId;
        if(!$objCSaleOrderPropsValue->Add($arFilter) && $bDebug){
            ShowMessage(array(
                "TYPE"=>"ERROR",
                "MESSAGE"=>"Ошибка изменения свойства ЗНИ"
            ));
            die;
        }
    }
    if(!CSaleOrderChange::AddRecord(
        $nOrderId,
        "ORDER_ZNI",
        array(
            "STATUS_ID"=>$sStatusId,
            "OLD_STATUS_ID"=>$sOldStatusId
        ),
        "ORDER"
    )){
        
    }
}
