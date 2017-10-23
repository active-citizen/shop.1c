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
                `ID`, `LOGIN`, `NAME`, `LAST_NAME`, `EMAIL`
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
