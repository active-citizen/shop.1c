<?
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
                    `ADDITIONAL_INFO`='".$DB->ForSql($arOrder["ADDITIONAL_INFO"])."'
                WHERE
                    `ID`='".$arOrder["ID"]."'
                LIMIT 1
            ";
        }
        else{
            $sQuery = "
                INSERT INTO `index_order`(
                    `ID`,`USER_ID`,`STORE_ID`,`STATUS_ID`
                    ,`DATE_INSERT`,`DATE_UPDATE`,`DATE_STATUS`
                    ,`ADDITIONAL_INFO`
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
    }


