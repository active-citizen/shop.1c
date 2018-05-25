<?php
    namespace Sync;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    
    use AGShop;
    use AGShop\DB as DB;
    
    class CSync extends \AGShop\CAGShop{
    /**
        Синхронизация пользователя в общей таблице и индексной
    */
    function syncUser($nUserId){
        global $DB;
        $sQuery = "
            SELECT
                `ID`, `LOGIN`, `NAME`, `LAST_NAME`, `EMAIL`
            FROM
                `b_user` as `user`
            WHERE
                `user`.`ID`=".$DB->ForSql($nUserId)."
            LIMIT 
                1
        ";
        $arUser = $DB->Query($sQuery)->Fetch();

        $sQuery = "
            SELECT
                `ID`
            FROM
                `index_user` as `user`
            WHERE
                `user`.`ID`=".$DB->ForSql($nUserId)."
            LIMIT 
                1
        ";
        $arExistsUser = $DB->Query($sQuery)->Fetch();

        if($arExistsUser){
            $sQuery = "
                UPDATE
                    `index_user`
                SET
                    `LOGIN`='".$DB->ForSql($arUser["LOGIN"])."',
                    `NAME`='".$DB->ForSql($arUser["NAME"])."',
                    `LAST_NAME`='".$DB->ForSql($arUser["LAST_NAME"])."',
                    `EMAIL`='".$DB->ForSql($arUser["EMAIL"])."'
                WHERE
                    `ID`='".$DB->ForSql($arUser["ID"])."'
                LIMIT 1
            ";
        }
        else{
            $sQuery = "
                INSERT INTO `index_user`(`ID`,`LOGIN`,`NAME`,`LAST_NAME`,`EMAIL`)
                VALUES(
                    '".$DB->ForSql($arUser["ID"])."',
                    '".$DB->ForSql($arUser["LOGIN"])."',
                    '".$DB->ForSql($arUser["NAME"])."',
                    '".$DB->ForSql($arUser["LAST_NAME"])."',
                    '".$DB->ForSql($arUser["EMAIL"])."'
                )
            ";
        }
        $DB->Query($sQuery);
    }

    /**
        Синхронизация заказа в индексной таблице и общей
    */
    function syncOrder($nOrderId){
        global $DB;

        $sQuery = "
            SELECT
                `ID`,
                `USER_ID`,
                `STORE_ID`,
                `STATUS_ID`,
                `DATE_INSERT`,
                `ADDITIONAL_INFO`,
                `DATE_UPDATE`,
                `DATE_STATUS`
            FROM
                `b_sale_order`
            WHERE
                `ID`='".$DB->ForSql($nOrderId)."'
            LIMIT
                1
        ";
        $arOrder = $DB->Query($sQuery)->Fetch();

        //Получаем свойсва заказа
        $arProps = [];
        $sQuery = "SELECT CODE FROM `b_sale_order_props` WHERE 1";
        $resProps = $DB->Query($sQuery);
        while($arProp = $resProps->Fetch())
            $arProps[$arProp["CODE"]] = "";

        // Получаем свойства конкретного заказа
        $sQuery = "SELECT CODE,VALUE FROM `b_sale_order_props_value` WHERE
        `ORDER_ID`=".$arOrder["ID"];
        $resProps = $DB->Query($sQuery);

        while($arProp = $resProps->Fetch())
            $arProps[$arProp["CODE"]] = $arProp["VALUE"];

        // Находим ID прозукта
        $sQuery = "
            SELECT
               `prod_link`.`VALUE_NUM` as `ID`,
               `basket`.`QUANTITY` as `QUANTITY`
            FROM
                `b_sale_basket` as `basket`
                    LEFT JOIN
                `b_iblock_element_property` as `prod_link`
                    ON
                    `basket`.`PRODUCT_ID`=`prod_link`.`IBLOCK_ELEMENT_ID`
                    AND
                    `IBLOCK_PROPERTY_ID` = ".CML2_LINK_PROPERTY_ID." 
            WHERE
                `basket`.`ORDER_ID`=".$arOrder["ID"]."
        ";
        $arProduct = $DB->Query($sQuery)->Fetch();

        $sQuery = "
            SELECT
               `man_link`.`VALUE_NUM` as `ID`
            FROM
                `b_iblock_element_property` as `man_link`
            WHERE
                `man_link`.`IBLOCK_ELEMENT_ID`=".$arProduct["ID"]."
                AND
                `IBLOCK_PROPERTY_ID` = ".MANUFACTURER_PROPERTY_ID." 
            LIMIT
                1
            
        ";
        $arMan = $DB->Query($sQuery)->Fetch();

        $sQuery = "
            SELECT
                `ID`
            FROM
                `index_order`
            WHERE
                `ID`='".$DB->ForSql($nOrderId)."'
            LIMIT
                1
        ";
        $arExistsOrder = $DB->Query($sQuery)->Fetch();

        if($arExistsOrder){
            $sQuery = "
                UPDATE
                    `index_order`
                SET
                    `USER_ID`='".$DB->ForSql($arOrder["USER_ID"])."',
                    `STORE_ID`='".$DB->ForSql($arOrder["STORE_ID"])."',
                    `STATUS_ID`='".$DB->ForSql($arOrder["STATUS_ID"])."',
                    `DATE_INSERT`='".$DB->ForSql($arOrder["DATE_INSERT"])."',
                    `DATE_UPDATE`='".$DB->ForSql($arOrder["DATE_UPDATE"])."',
                    `DATE_STATUS`='".$DB->ForSql($arOrder["DATE_STATUS"])."',
                    `ADDITIONAL_INFO`='".$DB->ForSql($arOrder["ADDITIONAL_INFO"])."',
                    `CLOSE_DATE`='".$DB->ForSql($arProps["CLOSE_DATE"])."',
                    `TROIKA_NUM`='".$DB->ForSql($arProps["TROIKA"])."',
                    `TROIKA_TRANSACT`='".$DB->ForSql(
                        $arProps["TROIKA_TRANSACT_ID"])."',
                    `PARKING_TRANSACT`='".$DB->ForSql(
                        $arProps["PARKING_TRANSACT_ID"])."',
                    `MAN_ID`='".$DB->ForSql($arMan["ID"])."',
                    `SECTION_ID`='".$DB->ForSql($arProps["SECTION_ID"])."',
                    `SECTION_NAME`='".$DB->ForSql($arProps["SECTION_NAME"])."',
                    `MAN_NAME`='".$DB->ForSql($arProps["MANUFACTURER_NAME"])."',
                    `PRODUCT_NAME`='".$DB->ForSql($arProps["PRODUCT_NAME"])."',
                    `PRODUCT_ID`='".$DB->ForSql($arProduct["ID"])."',
                    `PROMOCODES`='".$DB->ForSql($arProps["PROMOCODES"])."',
                    `INFOTECH_ORDER_ID`='".$DB->ForSql($arProps["INFOTECH_ORDER_ID"])."',
                    `QUANTITY`='".($arProduct["QUANTITY"])."'

                WHERE
                    `ID`='".$arOrder["ID"]."'
                LIMIT 1
            ";
        }
        else{
            $sQuery = "
                INSERT INTO `index_order`(
                    `ID`
                    ,`USER_ID`
                    ,`STORE_ID`
                    ,`STATUS_ID`
                    ,`DATE_INSERT`
                    ,`DATE_UPDATE`
                    ,`DATE_STATUS`
                    ,`ADDITIONAL_INFO`
                    ,`CLOSE_DATE`
                    ,`TROIKA_NUM`
                    ,`TROIKA_TRANSACT`
                    ,`PARKING_TRANSACT`
                    ,`PRODUCT_ID`
                    ,`SECTION_ID`
                    ,`MAN_ID`
                    ,`PRODUCT_NAME`
                    ,`SECTION_NAME`
                    ,`MAN_NAME`
                    ,`PROMOCODES`
                    ,`INFOTECH_ORDER_ID`
                    ,`QUANTITY`
                )
                VALUES(
                    '".$DB->ForSql($arOrder["ID"])."'
                    ,'".$DB->ForSql($arOrder["USER_ID"])."'
                    ,'".$DB->ForSql($arOrder["STORE_ID"])."'
                    ,'".$DB->ForSql($arOrder["STATUS_ID"])."'
                    ,'".$DB->ForSql($arOrder["DATE_INSERT"])."'
                    ,'".$DB->ForSql($arOrder["DATE_UPDATE"])."'
                    ,'".$DB->ForSql($arOrder["DATE_STATUS"])."'
                    ,'".$DB->ForSql($arOrder["ADDITIONAL_INFO"])."'
                    ,'".$DB->ForSql($arProp["CLOSE_DATE"])."'
                    ,'".$DB->ForSql($arProps["TROIKA"])."'
                    ,'".$DB->ForSql($arProps["TROIKA_TRANSACT_ID"])."'
                    ,'".$DB->ForSql($arProps["PARKING_TRANSACT_ID"])."'
                    ,'".$DB->ForSql($arProduct["ID"])."'
                    ,'".$DB->ForSql($arProp["SECTION_ID"])."'
                    ,'".$DB->ForSql($arMan["ID"])."'
                    ,'".$DB->ForSql($arProps["PRODUCT_NAME"])."'
                    ,'".$DB->ForSql($arProps["SECTION_NAME"])."'
                    ,'".$DB->ForSql($arProps["MANUFACTURER_NAME"])."'
                    ,'".$DB->ForSql($arProps["PROMOCODES"])."'
                    ,'".$DB->ForSql($arProps["INFOTECH_ORDER_ID"])."'
                    ,'".$DB->ForSql($arProduct["QUANTITY"])."'
                )
            ";
        }
        $DB->Query($sQuery);
    }


    function syncAllUsers(){
        global $DB;
        $resUsers = $DB->Query("SELECT `ID` FROM `b_user` ORDER BY `ID` DESC");
        while($arUser = $resUsers->Fetch())
            syncUser($arUser["ID"]);
        
    }

    function syncAllOrders(){
        global $DB;
        $resOrders = $DB->Query("SELECT `ID` FROM `b_sale_order` ORDER BY `ID` DESC");
        while($arOrder = $resOrders->Fetch())
            syncOrder($arOrder["ID"]);
    }    }
    
