<?
    /**
        Синхронизация пользователя в общей таблице и индексной
    */
    function syncUsers($nUserId){
        global $DB;
        $sQuery = "
            SELECT
                `ID`, `LOGIN`, `NAME`, `LAST_NAME`, `EMAIL`
            FROM
                `b_users` as `user`
            WHERE
                `user`.`ID`=".$DB->ForSql($nUserId)."
        ";
        echo $sQuery;

    }
