<?php
require_once(
   $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"
);

if(!defined("QUERY_LOCK_FILE"))
    define(
        "QUERY_LOCK_FILE",
        $sFilename = realpath($_SERVER["DOCUMENT_ROOT"]."/..")."/tmp/query.lock"
    );

/**
    Обновление свойств у заказа
*/
function orderPropertiesUpdate(
    $nOrderId, 
    $bDebug = false,
    $sCode = '',
    $sValue = ''
){
    global $DB;
    $objCSaleOrderPropsValue = new CSaleOrderPropsValue;
    
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

    if($sCode){
        $arProp = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            array(
                    "ORDER_ID"          => $arOrder["ID"],
                    "PERSON_TYPE_ID"    => 1,
                    "PROPS_GROUP_ID"    => $nPropGroup,
                    "CODE"              => $sCode
                ),
            false,
            false,
            array("ID","CODE","NAME")
        )->Fetch();


        $arFilter = array(
            "ORDER_ID"=>$arOrder["ID"],
            "ORDER_PROPS_ID"=>$arProp["ID"],
            "CODE"=>$sCode,
            "NAME"=>$arProp["NAME"]
        );
    
        if(
            $arExistPropValue = 
            CSaleOrderPropsValue::GetList(Array(), $arFilter)->Fetch()
        ){
            $arFilter["VALUE"] = $sValue;
            if($bDebug){
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
        elseif($sValue){
            $arFilter["VALUE"] = $sValue;
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
        return true;
    }

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
        $sQuery = "
            SELECT
                `ID`,
                `VALUE`
            FROM
                `b_sale_order_props_value`
            WHERE
                `ORDER_ID`='".$arOrder["ID"]."'
                AND
                `ORDER_PROPS_ID`='".$arProp["ID"]."'
            LIMIT 
                1
        ";
        $arValue = $DB->Query($sQuery)->Fetch();
        /*
        Во имя оптимизации
        $arValue =  
            CSaleOrderPropsValue::GetList(
                array(),
                $arFilterProp = array(
                    "ORDER_ID"=>$arOrder["ID"],
                    "ORDER_PROPS_ID"=>$arProp["ID"]
                ),
                false,
                array("nTopCount"=>1),
                array("VALUE_ORIG","VALUE")
            )->GetNext();
        */

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


    $objCSaleOrderPropsValue = new CSaleOrderPropsValue;
//    $bDebug = true;
    foreach($arOrder["PROPERTIES"] as $sPropName=>$arPropValue){
        $arFilter = array(
            "ORDER_ID"      =>  $arOrder["ID"],
            "ORDER_PROPS_ID"=>  $arPropValue["PROPERTY_SETTINGS"]["ID"],
            "CODE"          =>  $sPropName,
            "NAME"          =>  $arPropValue["PROPERTY_SETTINGS"]["NAME"]
        );

        $sQuery = "
            SELECT
                `ID`,
                `VALUE`
            FROM
                `b_sale_order_props_value`
            WHERE
                `ORDER_ID`='".$arFilter["ORDER_ID"]."'
                AND
                `ORDER_PROPS_ID`='".$arFilter["ORDER_PROPS_ID"]."'
            LIMIT 
                1
        ";
        // Ищем существующее значение
        $arExistPropValue = 
        $DB->Query($sQuery)->Fetch();
        // Обновляем существующее значение, если оно отличается от предлагаемого
        if(
            isset($arExistPropValue["VALUE"])
            &&
            $arExistPropValue["VALUE"]
            &&
            $arExistPropValue["VALUE"]!= $arPropValue["PROPERTY_VALUE"]
            /*
            CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
            */
        ){
            $arFilter["VALUE"] = $arPropValue["PROPERTY_VALUE"];
            if($bDebug){
//              echo "Edit\n";
//              print_r($arFilter);
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
        elseif(
            !isset($arExistPropValue["VALUE"])
            &&
            $arPropValue["PROPERTY_VALUE"]
        ){
            $arFilter["VALUE"] = $arPropValue["PROPERTY_VALUE"];
            if($bDebug){
//            echo "Add\n";
//            print_r($arFilter);
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
        $sStatusId?"ORDER_ZNI":"ORDER_ZNI_CHECK",
        array(
            "STATUS_ID"=>$sStatusId,
            "OLD_STATUS_ID"=>$sOldStatusId
        ),
        "ORDER"
    )){
        
    }
}

/*
    Установка сесси обмена
*/
function orderSetSessionId($nOrderId,$sSessionId){

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
        "ORDER_PROPS_ID"=>  $PROPERTIES["SESSION_ID"]["ID"],
        "CODE"          =>  "SESSION_ID",
        "NAME"          =>  $PROPERTIES["SESSION_ID"]["NAME"],
    );

   
    if(
        $arExistPropValue = 
        CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
    ){
        $arFilter["VALUE"] = $sSessionId;
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
    else{
        $arFilter["VALUE"] = $sSessionId;
        if(!$objCSaleOrderPropsValue->Add($arFilter) && $bDebug){
            ShowMessage(array(
                "TYPE"=>"ERROR",
                "MESSAGE"=>"Ошибка изменения свойства ЗНИ"
            ));
            die;
        }
    }
}



/**
    Получение свойств у заказа
*/
function orderGetProperties(
    $nOrderId,  //<! ID заказа
    $arSelect = array() // Массив имён свойст, то надо вытащить
){
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
       $PROPERTIES[$arProp["CODE"]] =       
           CSaleOrderPropsValue::GetList(        
               array(),      
               $arFilterProp = array(        
                   "ORDER_ID"=>$nOrderId,     
                   "ORDER_PROPS_ID"=>$arProp["ID"]       
               )     
           )->GetNext();     
   }
   return $PROPERTIES;
    
}

/**
    Определение сколько в этом месяце пользователь заказал товара
*/
function getMounthProductCount(
    $nUserId,
    $nProductId
){
    global $DB;
    $nUserId = intval($nUserId);
    $nPropuctId = intval($nProductId);

    // Вычисляем ID свойства привязки к элементу каталога
    
    $sQuery = "
        SELECT
            `ID` as `id`
        FROM
            `b_iblock_property` as `a`
        WHERE
            `a`.`IBLOCK_ID`=".OFFER_IB_ID."
            AND `a`.`CODE`='CML2_LINK'
        LIMIT 
            1
    ";

    $arProp = $DB->Query($sQuery)->Fetch();
    $nPropId = isset($arProp["id"])?$arProp["id"]:0;
    $sStartDate = date("Y-m-d H:i:s",mktime(
        date("H"),date("i"),date("s"),
        date("m")-1,date("d"),date("Y")
    ));

    $sQuery = "
        SELECT
            count(`b`.`ID`) as `count`,
            DATE_FORMAT(DATE_ADD(`a`.`DATE_INSERT`, INTERVAL 1 MONTH),'%d.%m.%Y %H:%i:%s') as `next`
            -- ,`a`.`DATE_INSERT` as `order_date`
            -- ,`c`.`VALUE_NUM` as `product_id`
            -- ,`a`.`ID` as `order_id`
            -- ,`b`.`PRODUCT_ID` as `offer_id`
        FROM 
            `b_iblock_element_property` as `c`
                LEFT JOIN
            `b_sale_basket` as `b`
                ON `b`.`PRODUCT_ID`=`c`.`IBLOCK_ELEMENT_ID`
                LEFT JOIN
            `b_sale_order` as `a`
                on `b`.`ORDER_ID`=`a`.`ID`

        WHERE
            1
            AND `c`.`IBLOCK_PROPERTY_ID`=$nPropId
            AND `c`.`VALUE_NUM`=$nProductId
            AND `a`.`USER_ID`=$nUserId
            AND `a`.`STATUS_ID` IN ('F','AA','N')
            AND `a`.`DATE_INSERT`>'$sStartDate'
        LIMIT
            1
    ";
    $arQuery = $DB->Query($sQuery)->Fetch();
    return [
        "next"  =>  isset($arQuery["next"])?$arQuery["next"]:date("d.m.Y H:i:s"),
        "count" =>  isset($arQuery["count"])?$arQuery["count"]:0
    ]; 
}

/**
    Проверка исчерпания месячного лимита на товар для пользователя
*/
function failedMonLimit(
    $nUserId,
    $nOfferId
){
    $nOfferId = intval($nOfferId);
    $arOffer = CIBlockElement::GetList(
        array(),
        $arFilter = array(
            "IBLOCK_ID" => OFFER_IB_ID,
            "ID"        =>  $nOfferId
        ),
        false,
        array("nTopCount"=>1),
        array("PROPERTY_CML2_LINK","ID")
    )->Fetch();
    if(!isset($arOffer["ID"]))return 1;

    $arProduct = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_ID" =>  CATALOG_IB_ID,
            "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"]
        ),
        false,
        array("nTopCount"=>1),
        array("PROPERTY_MON_LIMIT","ID")
    )->Fetch();

    $arFailedLimit = 
    getMounthProductCount(
        $nUserId,
        $arProduct["ID"]
    );
    $failedLimit = $arFailedLimit["count"];

    if(
        $arProduct["PROPERTY_MON_LIMIT_VALUE"]
        &&
        $failedLimit >= $arProduct["PROPERTY_MON_LIMIT_VALUE"]

    )
    return $arProduct["PROPERTY_MON_LIMIT_VALUE"];
}

/**
    Получение списка заказов по фильтру
*/
function getDownloadOrders(
    $arFilter,
    $arOrder,
    $bOnlyCount=true, 
    $nLimit=0,
    $nOffset=0
){
    global $DB;

    // Справочник статусов
    $resStatuses = CSaleStatus::GetList();
    $arStatuses = [];
    while($arStatus = $resStatuses->Fetch())
        $arStatuses[$arStatus["ID"]] = $arStatus;

    // Справочник центров выдачи
    $resStores  = CCatalogStore::GetList(
        array(),
        array(),
        false,false
    );
    $arStores = array();
    while($arStore = $resStores->GetNext()){
        $arStores[$arStore["ID"]] = $arStore;
    }


    // Составляем справочник свойств
    $sQuery = "SELECT `ID`,`CODE` FROM `b_sale_order_props`";        
    $res = $DB->Query($sQuery);
    $arProps = [];
    while($arProp = $res->Fetch())$arProps[$arProp["CODE"]]=$arProp["ID"];

    $sFrom = "
        `index_order` as `order`";
    $sFrom .= "
        LEFT JOIN
    `index_user` as `user`
        ON
            `user`.`ID`=`order`.`USER_ID` ";
    $sFrom .= "
        LEFT JOIN
    `b_iblock_element_property` as `price`
        ON
            `price`.`IBLOCK_PROPERTY_ID`=".PRICE_PROPERTY_ID."
            AND `order`.`PRODUCT_ID`=`price`.`IBLOCK_ELEMENT_ID`";


    $sWhere = "
        1";


    if(isset($arFilter["STATUS_ID"]))
        $sWhere .= " 
        AND `order`.`STATUS_ID`='".$DB->ForSql($arFilter["STATUS_ID"])."'";

    if(isset($arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"])){
         if(is_array($arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"]))
            $sWhere .= "
                AND `order`.`MAN_ID` IN ("
                    .$DB->ForSql(
                        implode(",",
                            $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"]
                        )
                    )
                .")";
         else
            $sWhere .= "
                AND `order`.`MAN_ID`= '"
                    .$DB->ForSql($arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"])
                ."'";
            
    }



    if(isset($arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
        $sWhere .= "
            AND `order`.`CLOSE_DATE`>= '"
                .ConvertDateTime(
                    $arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"][0],
                    "YYYY-MM-DD",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."' 
            AND `order`.`CLOSE_DATE`<= '"
                .ConvertDateTime(
                    $arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"][1],
                    "YYYY-MM-DD",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
    }
    if(isset($arFilter[">PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
        $sWhere .= "
                AND `order`.`CLOSE_DATE`>= '"
                    .ConvertDateTime(
                        $arFilter[">PROPERTY_VAL_BY_CODE_CLOSE_DATE"],
                        "YYYY-MM-DD",
                        "DD.MM.YYYY HH:MI:SS"
                    )
                ."'";

    }
    if(isset($arFilter["<PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
        $sWhere .= "
                AND `order`.`CLOSE_DATE`<='"
                    .ConvertDateTime(
                        $arFilter["<PROPERTY_VAL_BY_CODE_CLOSE_DATE"],
                        "YYYY-MM-DD",
                        "DD.MM.YYYY HH:MI:SS"
                    )
                ."'";
    }


    if(isset($arFilter["STORE_ID"])){
        if(is_array($arFilter["STORE_ID"]))
            $sWhere .= "
            AND `order`.`STORE_ID` IN ("
                .$DB->ForSql(implode(",",$arFilter["STORE_ID"]))
            .")";
        else
            $sWhere .= "
            AND `order`.`STORE_ID`=".intval($arFilter["STORE_ID"])."";
    }

    if(isset($arFilter["><DATE_INSERT"])){
        $sWhere .= "
        AND `order`.`DATE_INSERT`>='"
            .ConvertDateTime(
                $arFilter["><DATE_INSERT"][0],
                "YYYY-MM-DD 00:00:00",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'
        AND `order`.`DATE_INSERT`<='"
            .ConvertDateTime(
                $arFilter["><DATE_INSERT"][1],
                "YYYY-MM-DD 23:59:59",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."' ";
    }
    if(isset($arFilter[">DATE_INSERT"])){
        $sWhere .= "
        AND `order`.`DATE_INSERT`>='"
            .ConvertDateTime(
                $arFilter[">DATE_INSERT"],
                "YYYY-MM-DD 00:00:00",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'";
    }
    if(isset($arFilter["<DATE_INSERT"])){
        $sWhere .= "
        AND `order`.`DATE_INSERT`<='"
            .ConvertDateTime(
                $arFilter["<DATE_INSERT"],
                "YYYY-MM-DD 23:59:59",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'";
    }


    if(isset($arFilter["><DATE_UPDATE"])){
        $sWhere .= "
        AND `order`.`DATE_UPDATE`>='"
            .ConvertDateTime(
                $arFilter["><DATE_UPDATE"][0],
                "YYYY-MM-DD 00:00:00",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'
        AND `order`.`DATE_UPDATE`<='"
            .ConvertDateTime(
                $arFilter["><DATE_UPDATE"][1],
                "YYYY-MM-DD 23:59:59",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."' ";
    }
    if(isset($arFilter[">DATE_UPDATE"])){
        $sWhere .= "
        AND `order`.`DATE_UPDATE`>='"
            .ConvertDateTime(
                $arFilter[">DATE_UPDATE"],
                "YYYY-MM-DD 00:00:00",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'";
    }
    if(isset($arFilter["<DATE_UPDATE"])){
        $sWhere .= "
        AND `order`.`DATE_UPDATE`<='"
            .ConvertDateTime(
                $arFilter["<DATE_UPDATE"],
                "YYYY-MM-DD 23:59:59",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'";
    }

    if(isset($arFilter[">=DATE_STATUS"])){
        $sWhere .= "
        AND `order`.`DATE_STATUS`>='"
            .ConvertDateTime(
                $arFilter[">=DATE_STATUS"],
                "YYYY-MM-DD 00:00:00",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'";
    }

    if(isset($arFilter["<=DATE_STATUS"])){
        $sWhere .= "
        AND `order`.`DATE_STATUS`<='"
            .ConvertDateTime(
                $arFilter["<=DATE_STATUS"],
                "YYYY-MM-DD 23:59:59",
                "DD.MM.YYYY HH:MI:SS"
            )
        ."'";
    }

    if(isset($arFilter["%USER_LOGIN"])){
        $sWhere .= "
        AND `user`.`LOGIN` LIKE '%".$arFilter["%USER_LOGIN"]."%' ";
    }

    if(isset($arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"])){
        $sWhere .="
        AND
        (
            `user`.`NAME` LIKE '%"
                .$DB->ForSql($arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"])
                ."%'
            OR
            `user`.`LAST_NAME` LIKE '%"
                .$DB->ForSql($arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"])
            ."%'
        ) ";
    }

    $sGroupBy = "";

    $sQuery = "
        SELECT
            COUNT(DISTINCT `order`.`ID`) as `COUNT`
        FROM
            $sFrom
        WHERE
            $sWhere
    ";
    $resOrder = $DB->Query($sQuery);
    if($bOnlyCount){
        $arOrder = $resOrder->Fetch();
        return $arOrder["COUNT"];
    }

    if($nOffset && $nLimit)
        $sLimit = "LIMIT $nOffset, $nLimit";
    elseif($nLimit)
        $sLimit = "LIMIT $nLimit";
    else
        $sLimit = "LIMIT 10";

    if(isset($arOrder)){
        $sOrder = '';
        foreach($arOrder as $sField=>$sDirection)
            $sOrder .= $sField." ".$sDirection.",";
        $sOrder .= " `order`.`ID` ASC";
    }
    else{
        $sOrder = "`order`.`ID` ASC";
    }

    $sQuery = "
        SELECT
            `order`.`ID` as `ORDER_ID`,
            `order`.`ADDITIONAL_INFO` as `ORDER_NUM`,
            `user`.`NAME` as `USER_NAME`,
            `user`.`LAST_NAME` as `USER_LAST_NAME`,
            `user`.`EMAIL` as `USER_EMAIL`,
            `order`.`STATUS_ID` as `STATUS_ID`,
            DATE_FORMAT(`order`.`DATE_INSERT`,'%d.%m.%Y %H:%i:%s') as `DATE_INSERT`,
            DATE_FORMAT(`order`.`DATE_UPDATE`,'%d.%m.%Y %H:%i:%s') as `DATE_UPDATE`,
            DATE_FORMAT(`order`.`DATE_STATUS`,'%d.%m.%Y %H:%i:%s') as `DATE_STATUS`,
            `user`.`LOGIN` as `USER_LOGIN`,
            `order`.`PRODUCT_NAME` as `PRODUCT_NAME`,
            `order`.`STORE_ID` as `STORE_ID`,
            `order`.`SECTION_NAME` as `SECTION_NAME`,
            `order`.`MAN_NAME` as `MANUFACTURER_NAME`,
            `price`.`VALUE_NUM` as `PRICE`,
            `order`.`CLOSE_DATE` as `CLOSE_DATE`
        FROM
            $sFrom
        WHERE
            $sWhere
        $sGroupBy
        ORDER BY
            $sOrder
        $sLimit
    ";
    $res = $DB->Query($sQuery);

//        echo "<pre>";
//        echo $sQuery;
//        die;

    $arOrders = [];
    while($arOrder = $res->Fetch()){
        $arOrder["STORE_NAME"] = $arStores[$arOrder["STORE_ID"]]["TITLE"];
        $arOrder["STATUS_NAME"] = $arStatuses[$arOrder["STATUS_ID"]]["NAME"];
        $arOrders[] = $arOrder;
    }

    return $arOrders;
}

/**
*/
function orderStorageChange(
    $nOfferId,  // У какого торгового предложения
    $nStoreId, // На каком складе
    $nCount     // На сколько (+1 , -1)
){
    global $DB;  
    $nOfferId = intval($nOfferId);
    $nStoreId = intval($nStoreId);
    $sQuery = "
        UPDATE
            `b_catalog_store_product`
        SET
            `AMOUNT`=`AMOUNT`+$nCount
        WHERE
            `PRODUCT_ID`= $nOfferId
            AND 
            `STORE_ID`= $nStoreId
    ";
    $DB->Query($sQuery);
}

/*
    @return количество товара на складе
*/
function orderStorageAmount(
    $nOfferId,  // У какого торгового предложения
    $nStoreId  // На каком складе
){
    global $DB;
    $nOfferId = intval($nOfferId);
    $nStoreId = intval($nStoreId);
    $sQuery = "
        SELECT
            ID,AMOUNT
        FROM
            `b_catalog_store_product`
        WHERE
            `PRODUCT_ID`= $nOfferId
            AND 
            `STORE_ID`= $nStoreId
    ";

    $arProductStore = $DB->Query($sQuery)->Fetch();
    if(isset($arProductStore["AMOUNT"]))
        return $arProductStore["AMOUNT"];
    return 0;
}

/**
    Проверка блокировки выгрузки заказов в 1С
*/
function orderQueryIsLocked(
    $lockTime = 30  // Время жизни блокировки
){
    if(!file_exists(QUERY_LOCK_FILE))return false;
    $arStat = stat(QUERY_LOCK_FILE);
    if(
        isset($arStat["mtime"])
        &&
        $arStat["mtime"]+$lockTime<time()
    ){
        return false;
    }
    elseif(!isset($arStat)){
        return false;
    }
    return (($arStat["mtime"]+$lockTime)-time());
}

/**
    Установка блокировки выгрузки заказов в 1С
*/
function orderQuerySetLock(){
    $fd = fopen(QUERY_LOCK_FILE,"w");
    fwrite($fd, print_r($_SERVER, 1));
    fclose($fd);
    return true;
}


/**
    Сброс блокировки выгрузки заказов в 1С
*/
function orderQueryResetLock(){
    //unlink(QUERY_LOCK_FILE);
    return true;
}
