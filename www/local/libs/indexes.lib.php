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
                    `LOGIN`='".$arUser["LOGIN"]."',
                    `NAME`='".$arUser["NAME"]."',
                    `LAST_NAME`='".$arUser["LAST_NAME"]."',
                    `EMAIL`='".$arUser["EMAIL"]."'
                WHERE
                    `ID`='".$arUser["ID"]."'
                LIMIT 1
            ";
        }
        else{
            $sQuery = "
                INSERT INTO `index_user`(`ID`,`LOGIN`,`NAME`,`LAST_NAME`,`EMAIL`)
                VALUES(
                    '".$arUser["ID"]."',
                    '".$arUser["LOGIN"]."',
                    '".$arUser["NAME"]."',
                    '".$arUser["LAST_NAME"]."',
                    '".$arUser["EMAIL"]."'
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
                    `USER_ID`='".$arOrder["USER_ID"]."',
                    `STORE_ID`='".$arOrder["STORE_ID"]."',
                    `STATUS_ID`='".$arOrder["STATUS_ID"]."',
                    `DATE_INSERT`='".$arOrder["DATE_INSERT"]."',
                    `DATE_UPDATE`='".$arOrder["DATE_UPDATE"]."',
                    `DATE_STATUS`='".$arOrder["DATE_STATUS"]."',
                    `ADDITIONAL_INFO`='".$arOrder["ADDITIONAL_INFO"]."'
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
                    '".$arOrder["ID"]."','".$arOrder["USER_ID"]."'
                    ,'".$arOrder["STORE_ID"]."','".$arOrder["STATUS_ID"]."'
                    ,'".$arOrder["DATE_INSERT"]."','".$arOrder["DATE_UPDATE"]."'
                    ,'".$arOrder["DATE_STATUS"]."','".$arOrder["ADDITIONAL_INFO"]."'
                )
            ";
        }
        $DB->Query($sQuery);

    }
