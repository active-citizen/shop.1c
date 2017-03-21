<?php
    require_once(realpath(dirname(__FILE__)."/../common.php"));
    require_once(realpath(dirname(__FILE__)."/wirix/db.class.php"));

    class CMigration{

        var $verbose = true;

        function __construct(){
        }


        /**
            Удаляет ключ таблицы

            @param $sName - имя таблицы
            @param $sColumnName - Имя ключа
            @param $bMulty - множественнная 
            @param $nDigits - Количество знаков суффикса
            @param $nBase - число значений разряда суффикса
            @param $sFormat - формат суффикса в терминах sprintf
        */
        function dropKey(
            $sTableName,        // Table name
            $sKeyName,          // Key name
            $bMulty     = false,// Multiply table
            $nDigits    = 2,    // Myltiply suffix digits
            $nBase      = 10,   // suffix digits base
            $sFormat    = 'x'   // Suffix digits format
        ){
            $nTotal = 1;
            if($bMulty)$nTotal = pow($nBase,$nDigits);

            for($i=0;$i<$nTotal;$i++){
                $query = "ALTER TABLE `$sTableName".(
                    $bMulty?"_".sprintf("%0$nDigits$sFormat",$i):""
                )."` DROP KEY `$sKeyName`;";
                if($this->verbose)echo $query."\n";
                $GLOBALS["DB"]->sql_query($query,"change"); 
            }
        }

        /**
            Удаляет столбец таблицы

            @param $sName - имя таблицы
            @param $sColumnName - столбец таблицы
            @param $bMulty - множественнная 
            @param $nDigits - Количество знаков суффикса
            @param $nBase - число значений разряда суффикса
            @param $sFormat - формат суффикса в терминах sprintf
        */
        function dropColumn(
            $sName,
            $sColumnName,
            $bMulty = false,
            $nDigits=2,
            $nBase=10,
            $sFormat = 'd'
        ){
            $nTotal = 1;
            if($bMulty)$nTotal = pow($nBase,$nDigits);

            for($i=0;$i<$nTotal;$i++){
                $query = "ALTER TABLE `$sName".(
                    $bMulty?"_".sprintf("%0$nDigits$sFormat",$i):""
                )."` DROP COLUMN `$sColumnName`;";
                if($this->verbose)echo $query."\n";
                $GLOBALS["DB"]->sql_query($query,"change"); 
            }
        }


        /**
            Удаляет таблицу, если существует со стандартными полями

            @param $sName - имя таблицы
            @param $bMulty - множественнная 
            @param $nDigits - Количество знаков суффикса
            @param $nBase - число значений разряда суффикса
            @param $sFormat - формат суффикса в терминах sprintf
        */
        function dropTable(
            $sName,
            $bMulty = false,
            $nDigits=2,
            $nBase=10,
            $sFormat = 'd'
        ){
            $nTotal = 1;
            if($bMulty)$nTotal = pow($nBase,$nDigits);

            for($i=0;$i<$nTotal;$i++){
                $query = "DROP TABLE IF EXISTS `$sName".(
                    $bMulty?"_".sprintf("%0$nDigits$sFormat",$i):""
                )."`;";
                if($this->verbose)echo $query."\n";
                $GLOBALS["DB"]->sql_query($query,"change"); 
            }
        }


        /**
            Создаёт таблицу, если не существует со стандартными полями
            id BIGINT(11) NOT TULL AUTO_INCREMENT,
            ctime DATETIME,
            mtime DATETIME

            @param $sName - имя таблицы
            @param $bMulty - множественнная 
            @param $nDigits - Количество знаков суффикса
            @param $nBase - число значений разряда суффикса
            @param $sFormat - формат суффикса в терминах sprintf
        */
        function addTable(
            $sName,
            $bMulty = false,
            $nDigits=2,
            $nBase=10,
            $sFormat = 'd'
        ){
            
            $nTotal = 1;
            if($bMulty)$nTotal = pow($nBase,$nDigits);

            for($i=0;$i<$nTotal;$i++){
                $query = "CREATE TABLE IF NOT EXISTS `$sName".(
                    $bMulty?"_".sprintf("%0$nDigits$sFormat",$i):""
                )."`(".
                    "`id` BIGINT(20) NOT NULL AUTO_INCREMENT, ".
                    "`ctime` DATETIME,".
                    "`mtime` DATETIME,".
                    "PRIMARY KEY `id`(`id`),".
                    "KEY `ctime`(`ctime`), ".
                    "KEY `mtime`(`mtime`)".
                ")ENGINE=MyISAM DEFAULT CHARSET=utf8";
                if($this->verbose)echo $query."\n";
                $GLOBALS["DB"]->sql_query($query,"change"); 
                
            }
        }

        /**
            Создаёт или изменяет поле в таблице

            @param $sTableName - Имя таблицы
            @param $sColumnName - Имя поля
            @param sType - Тип поля (например CHAR, INT...)
            @param $nSize - Размер поря (например 11, не ставить если не предусмотрено)
            @param $sDefault - Значение по умолчанию (false - если не нужно)
            @param $bUnsigned - беззнаковое
            @param $sComment - Комментарий к полю
            @param $isNull - Поле может принимать значение NULL
            @param $bMulty - множественнная 
            @param $nDigits - Количество знаков суффикса
            @param $nBase - число значений разряда суффикса
            @param $sFormat - формат суффикса в терминах sprintf
        */
        function setColumn(
            $sTableName,
            $sColumnName    =   'name',
            $sType          =   'char',
            $nSize          =   '32',
            $sDefault       =   false,
            $bUnsigned      =   false,
            $sComment       =   '',
            $bNull          =   false,
            $bMulty         =   false,
            $nDigits        =   2,
            $nBase          =   10,
            $sFormat        =   'd'
        ){
            if($sColumnName=='id')return false;

            $nTotal = 1;
            if($bMulty)$nTotal = pow($nBase,$nDigits);

            for($i=0;$i<$nTotal;$i++){
                $sTableNameIter = "$sTableName".(
                    $bMulty?"_".sprintf("%0$nDigits$sFormat",$i):"") ;

                $query = "SHOW COLUMNS 
                    FROM `$sTableNameIter` WHERE Field='$sColumnName'";
                $res = $GLOBALS["DB"]->sql_query($query,"select");

                $sSubQuery = "`$sColumnName` $sType";
                if($nSize)$sSubQuery.= " ($nSize) ";
                if($bUnsigned)$sSubQuery.= " UNSIGNED ";
                if(!$bNull)$sSubQuery.=" NOT NULL ";
                if($sDefault!==false)$sSubQuery.=" DEFAULT '$sDefault'";
                if($sComment)$sSubQuery.=" COMMENT '$sComment'";

                $sPreQuery = "ALTER TABLE `$sTableNameIter` ";

                if(!$res->num_rows){
                    $query = $sPreQuery." ADD COLUMN $sSubQuery";
                }
                else{
                    $query = $sPreQuery." CHANGE COLUMN `$sColumnName` $sSubQuery";
                }
                if($this->verbose)echo $query."\n";
                $GLOBALS["DB"]->sql_query($query,"change"); 
            }

        }

        /**
            Создаёт или изменяет ключи

            @param $sTableName  - Table name
            @param $sKeyName - key name
            @param $arFields - Fields
            @param $bUnique  - Is key Unique
            @param $bMulty   - is Multiply table
            @param $nDigits  - Myltiply suffix digits
            @param $nBase    - suffix digits base
            @param $sFormat  - Suffix digits format

        */
        function setkey(
            $sTableName,        // Table name
            $sKeyName,          // Key name
            $arFields,          // Fields
            $bUnique    = false,// Unique
            $bMulty     = false,// Multiply table
            $nDigits    = 2,    // Myltiply suffix digits
            $nBase      = 10,   // suffix digits base
            $sFormat    = 'x'   // Suffix digits format
        ){
            if(array_search("id", $arFields)!==false)return false;

            $nTotal = 1;
            if($bMulty)$nTotal = pow($nBase,$nDigits);

            for($i=0;$i<$nTotal;$i++){

                $sTableNameIter = "$sTableName".(
                    $bMulty?"_".sprintf("%0$nDigits$sFormat",$i):"") ;

                $query = "SHOW KEYS FROM `$sTableNameIter` WHERE `Key_name`='$sKeyName'";
                $res = $GLOBALS["DB"]->sql_query($query,"select");
                if($res->num_rows){
                    $query = "ALTER TABLE `$sTableNameIter` DROP KEY `$sKeyName`";
                    if($this->verbose)echo $query.";";
                    $GLOBALS["DB"]->sql_query($query,"change");
               }

                $sPreQuery = "ALTER TABLE `$sTableNameIter` ";
                $sFields = "(`".implode("`,`",$arFields)."`)";

                $query = $sPreQuery." ADD ".(
                    $bUnique?" UNIQUE ":""
                )." KEY `$sKeyName`$sFields";
                if($this->verbose)echo $query."\n";
                $GLOBALS["DB"]->sql_query($query,"change"); 
            }

        }



    }
