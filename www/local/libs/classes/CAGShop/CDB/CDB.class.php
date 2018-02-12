<?php
   namespace DB;
   require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
   use AGShop; 

   class CDB extends \AGShop\CAGShop{

        var $debug = false;
        /**
            Выполняет запросы из SQL-файла
            @param $sFilename - имя ыйд-afqkf
            @return 
        */
        function runSqlFile($sFilename){
            global $DB;

            if(!file_exists($sFilename)){
                $this->addError("File '$sFilename' is not exists");
                return false;
            }

            $arErrors = $DB->RunSqlBatch($sFilename);
            if($arErrors){
                $this->addError($arErrors);
                return false;
            }
            return true;
        }

        /**
            Поиск одной записи по имени поля
            @param $sTableName - имя таблицы
            @param $arFilter = ["имя поля"=>"значение",...]
            @return ["имя поля"=>"значение",...]
        */
        function searchOne($sTableName, $arFilter = [], $arSelect = []){
            global $DB;

            $sWhere = "1";
            foreach($arFilter as $sKey=>$sValue){
                $sWhere .= " AND `".$DB->ForSql($sKey)."`='"
                    .$DB->ForSql($sValue)."'";
            }

            if($arSelect)
                $sSelect = "`".implode("`,`",$arSelect)."`";
            else
                $sSelect = "*";

            $sQuery = "
                SELECT
                    $sSelect
                FROM
                    `$sTableName`
                WHERE
                    $sWhere
                LIMIT
                    1
            ";

            return array_pop($this->sqlSelect($sQuery));
        }

        /**
            Поиск всех записей по условию
            @param $sTableName - имя таблицы
            @param $arFilter = ["имя поля"=>"значение",...]
            @return [["имя поля"=>"значение","имя поля"=>"значение",...],...]
        */
        function searchAll($sTableName, $arFilter = [], $arSelect = []){
            global $DB;

            $sWhere = "1";
            foreach($arFilter as $sKey=>$sValue){
                $sWhere .= " AND `".$DB->ForSql($sKey)."`='"
                    .$DB->ForSql($sValue)."'";
            }

            if($arSelect)
                $sSelect = "`".implode("`,`",$arSelect)."`";
            else
                $sSelect = "*";

            $sQuery = "
                SELECT
                    $sSelect
                FROM
                    `$sTableName`
                WHERE
                    $sWhere
            ";

            return $this->sqlSelect($sQuery);
        }

        /**
            Удаление всех записей по условию
            @param $sTableName - имя таблицы
            @param $arFilter = ["имя поля"=>"значение",...]
            @return true
        */
        function delete($sTableName, $arFilter = []){
            global $DB;

            $sWhere = "1";
            foreach($arFilter as $sKey=>$sValue){
                $sWhere .= " AND `".$DB->ForSql($sKey)."`='"
                    .$DB->ForSql($sValue)."'";
            }

            $sQuery = "DELETE FROM `$sTableName` WHERE $sWhere";
            $this->sqlQuery($sQuery);

            return true;
        }

        /**
            Обновление всех записей по условию
            @param $sTableName - имя таблицы
            @param $arFilter = ["имя поля"=>"значение",...]
            @return true
        */
        function update($sTableName, $arFilter, $arFields){
            global $DB;

            $sWhere = "1";
            foreach($arFilter as $sKey=>$sValue){
                $sWhere .= " AND `".$DB->ForSql($sKey)."`='"
                    .$DB->ForSql($sValue)."'";
            }
            
            foreach($arFilter as $sFirstKey=>$sFirstValue)break;
            $sSet = "`$sFirstKey`=`$sFirstKey`";
            foreach($arFields as $sKey=>$sValue){
                $sSet .= " , `".$DB->ForSql($sKey)."`='"
                    .$DB->ForSql($sValue)."'";
            }
            

            $sQuery = "UPDATE `$sTableName` SET $sSet WHERE $sWhere";
            $this->sqlQuery($sQuery);

            return true;
        }

        /**
            Вставка записи в таблицу
        */
        function insert($sTable, $arFields){
            global $DB;
            $arKeys = [];
            $arValues = [];
            foreach($arFields as $sKey=>$sValue){
                $arKeys[] = $DB->ForSql($sKey);
                $arValues[] = $DB->ForSql($sValue);
            }
            $sFields = "`".implode("`,`",$arKeys)."`";
            $sValues = "'".implode("','",$arValues)."'";

            $sQuery = "INSERT INTO `"
                .$DB->ForSql($sTable)."`($sFields) VALUES($sValues)";

            if(!$this->sqlQuery($sQuery)){
                return false;
            }
            return $DB->LastID();
        }

        /**
            Выполнение SELECT-запроса
            @param $sQuery - текст запроса
            @param $nLimit - максимальное число возвражаемых записей. Игнорирует
            LIMIT из SQL-заапроса
            @return массив результата запроса
        */
        function sqlSelect($sQuery, $nLimit=100){

            if(!$resQuery = $this->sqlQuery($sQuery))return false;

            $nCounter = 0;
            $arReqult = [];
            while($arQuery = $resQuery->Fetch()){
                $nCounter++;
                $arResult[] = $arQuery;
                if($nCounter>=$nLimit)break;
            }
            return $arResult;
        }

        /**
            
        */
        function sqlQuery($sQuery){
            global $DB;
            if($this->debug){
                echo $sQuery;
            }
            if(!$resQuery = $DB->Query($sQuery, true)){
                $this->addError($DB->db_Conn->error);
                $this->addError("SQL query error: $sQuery");
                return false;
            }
            return $resQuery;            
        }

        /**
            
        */
        function ForSql($sText){
            global $DB;
            return $DB->ForSql($sText);
        }
   
   }
