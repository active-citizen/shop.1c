<?php
   namespace DB;
   require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
   use AGShop; 

   class CDB extends \AGShop\CAGShop{

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
   }
