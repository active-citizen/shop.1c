<?php
require_once(
   $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"
);

/**
    Получение списка заказов по фильтру для общей таблицы
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

    while($arProp = $res->Fetch()){
        $arProps[$arProp["CODE"]]=$arProp["ID"];
        if(isset($arFilter["PROPERTY_VALUE_".$arProp["ID"]])){
            $arFilter["PROPERTY_VAL_BY_CODE_".$arProp["CODE"]] = 
                $arFilter["PROPERTY_VALUE_".$arProp["ID"]];
            unset($arFilter["PROPERTY_VALUE_".$arProp["ID"]]);
        }
        if(isset($arFilter["%PROPERTY_VALUE_".$arProp["ID"]])){
            $arFilter["%PROPERTY_VAL_BY_CODE_".$arProp["CODE"]] = 
                $arFilter["%PROPERTY_VALUE_".$arProp["ID"]];
            unset($arFilter["%PROPERTY_VALUE_".$arProp["ID"]]);
        }
    }

    $sFrom = "
        `index_order` as `order`";
    $sFrom .= "
        LEFT JOIN
    `index_user` as `user`
        ON
            `user`.`ID`=`order`.`USER_ID` ";

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

    if(isset($arFilter["%USER_EMAIL"])){
        $sWhere .= "
        AND `user`.`EMAIL` LIKE '%".$arFilter["%USER_EMAIL"]."%' ";
    }

    if(isset($arFilter["%USER_LOGIN"])){
        $sWhere .= "
        AND `user`.`LOGIN` LIKE '%".$arFilter["%USER_LOGIN"]."%' ";
    }

    if(isset($arFilter["%ADDITIONAL_INFO"])){
        $sWhere .= "
        AND `order`.`ADDITIONAL_INFO` LIKE '%".$arFilter["%ADDITIONAL_INFO"]."%' ";
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

    if(isset($arFilter["%PROPERTY_VAL_BY_CODE_PRODUCT_NAME"])){
        $sWhere .="
            AND `order`.`PRODUCT_NAME` LIKE '%"
            .$arFilter["%PROPERTY_VAL_BY_CODE_PRODUCT_NAME"]
            ."%' ";
    }

    if(isset($arFilter["PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
        $sWhere .= "
            AND `order`.`CLOSE_DATE`= '"
                .$DB->ForSql($arFilter["PROPERTY_VAL_BY_CODE_CLOSE_DATE"])
            ."'";
    }

    if(isset($arFilter["PROPERTY_VAL_BY_CODE_SECTION_ID"])){
        $sWhere .= "
            AND `order`.`SECTION_ID`= '"
                .intval($arFilter["PROPERTY_VAL_BY_CODE_SECTION_ID"])
            ."'";
    }

    if(isset($arFilter["EXTRA"])){
        $sWhere .= "
            AND (
                `order`.`TROIKA_NUM`= '"
                    .$DB->ForSql($arFilter["EXTRA"])."'
                OR `order`.`TROIKA_TRANSACT`='"
                    .$DB->ForSql($arFilter["EXTRA"])."'
                OR `order`.`PARKING_TRANSACT`='"
                    .$DB->ForSql($arFilter["EXTRA"])."'
            )";
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

    if(isset($arOrder) && $arOrder){
        $sOrder = "`order`.`ID` DESC";
        $arOrderIndex = [
            "PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"=>"FIO",
            "ADDITIONAL_INFO"=>"ADDITIONAL_INFO",
            "STATUS_ID"=>"STATUS_ID",
            "DATE_INSERT"=>"`order`.`DATE_INSERT`",
            "USER_EMAIL"=>"USER_EMAIL",
            "PROPERTY_VAL_BY_CODE_PRODUCT_NAME"=>"PRODUCT_NAME",
            "USER_LOGIN"=>"USER_LOGIN",

        ];
        foreach($arOrder as $key=>$value)break;
        if(isset($arOrderIndex[$key]))
            $sOrder = $DB->ForSql($arOrderIndex[$key])
                ." ".$DB->ForSql($value);
    }
    else{
        $sOrder = "`order`.`ID` DESC";
    }

    $sQuery = "
        SELECT
            `order`.`ID` as `ID`,
            `order`.`QUANTITY` as `QUANTITY`,
            `order`.`ADDITIONAL_INFO` as `ADDITIONAL_INFO`,
            CONCAT(`user`.`LAST_NAME`,' ',`user`.`NAME`) as `FIO`,
            `user`.`EMAIL` as `USER_EMAIL`,
            `order`.`STATUS_ID` as `STATUS_ID`,
            DATE_FORMAT(`order`.`DATE_INSERT`,'%d.%m.%Y %H:%i:%s') as `DATE_INSERT`,
            `user`.`LOGIN` as `USER_LOGIN`,
            `order`.`PRODUCT_NAME` as `PRODUCT_NAME`,
            `order`.`SECTION_NAME` as `SECTION_NAME`,
            `order`.`CLOSE_DATE` as `CLOSE_DATE`,
            `order`.`TROIKA_NUM` as `TROIKA_NUM`,
            `order`.`TROIKA_TRANSACT` as `TROIKA_TRANSACT`,
            `order`.`PARKING_TRANSACT` as `PARKING_TRANSACT`,
            `order`.`PROMOCODES` as `PROMOCODES`
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

    $arOrders = [];
    while($arOrder = $res->Fetch()){
        $arOrder["STORE_NAME"] = $arStores[$arOrder["STORE_ID"]]["TITLE"];
        $arOrder["STATUS_NAME"] = $arStatuses[$arOrder["STATUS_ID"]]["NAME"];
        $arOrders[] = $arOrder;
    }

    return $arOrders;
}



