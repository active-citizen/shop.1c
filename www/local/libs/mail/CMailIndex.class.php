<?
    class CMailIndex{
        function add($nOrderId){
            global $DB;
            $sDate = date("Y-m-d H:i:s");

            $sId = sha1(time().$arFields["ORDER_ID"].rand());

            $sQuery = "
                INSERT INTO `index_mail`(
                    `ID`,
                    `DATE_CREATE`,
                    `ORDER_ID`
                )
                VALUES(
                    '".$sId."',
                    '".$sDate."',
                    '".$nOrderId."'
                )
            ";
            $DB->Query($sQuery);
    
            return $sId;
        }

        function setFilename($sId,$sFilename){
            global $DB;
            $sQuery = "
                UPDATE
                    `index_mail`
                SET
                    `FILENAME`='".$sFilename."'
                WHERE
                    `ID`='$sId'
                LIMIT 
                    1

            ";
            $DB->Query($sQuery);
        }

        function setSentDate($sId){
            global $DB;
            $sQuery = "
                UPDATE
                    `index_mail`
                SET
                    `DATE_SENT`='".date("Y-m-d H:i:s")."'
                WHERE
                    `ID`='$sId'
                LIMIT 
                    1
            ";
            $DB->Query($sQuery);
        }

        function setReceiveDate($sId){
            global $DB;
            $sQuery = "
                UPDATE
                    `index_mail`
                SET
                    `DATE_RECEIVE`='".date("Y-m-d H:i:s")."'
                WHERE
                    `ID`='$sId'
                LIMIT 
                    1
            ";
            $DB->Query($sQuery);
        }
    }
