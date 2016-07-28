<?
/*
 * storages.class.php
 * 
 * Copyright 2016 Андрей Инюцин <inutcin@yandex.ru>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
    require_once("classes/active-citizen-bridge.class.php");

    class bxStorages{
        
        var $errors = array();
        var $logs = array();
        
        /*
         * Заполнение промежуточной таблицы данными категорий из внешнего 
         * источника
         * @param - $updatePeriod - период обновления категорий 
         *      (0 - принудительное обновление)
         */
        function updateImportTable($updatePeriod = 3600){
            
            global $DB;
        
            // Проверяем, надо ли что-нибудь обновлять
            $needUpdate = 0;
            if($updatePeriod==0){
                $needUpdate = 1;
            }
            else{
                $query = "
                    SELECT 
                        count(*) as `count` 
                    FROM 
                        `int_storages_import` 
                    WHERE 
                        UNIX_TIMESTAMP(NOW())-`last_update`>$updatePeriod
                    LIMIT
                        1";
                $res = $DB->query($query);
                $row = $res->GetNext();
                if(isset($row['count']) && $row['count']>0)$needUpdate = 1;
            }
            
            // Обновлять ничего не надо - выходим
            if(!$needUpdate)return false;
            
            // Созжаём мост с другими API
            $agBrige = new ActiveCitizenBridge;

            // Список аргументов
            $args = array();
            // Метод моста
            $agBrige->setMethod('getStorages');
            // Режим моста
            $agBrige->setMode('arm');
            // Аргументы
            $agBrige->setArguments($args);
            // Ошибки, возникшие во время установки параметров моста
            $this->errors = $agBrige->getErrors();
            
            // Если ошибок нет - выполняем установленным метод моста
            if(!$this->errors && !$storages = $agBrige->exec()){
                $this->errors = array_merge(
                    $this->errors, $agBrige->getErrors()
                );
            }
            
            // Составляем индекс текущего содержимого промежуточной таблицы
            // Ключ - ID внешнего источника данных
            $indexStorages = array();
            $query = "SELECT * FROM `int_storages_import`";
            $res = $DB->Query($query);
            while($row = $res->GetNext())
                $indexStorages[$row["external_id"]] = $row;
              
            
            
            // Перебираем полученные от моста склады
            foreach($storages as $storage){
                // Подготавливаем все параметры склада для работы с SQL
                foreach($storage as $k=>$v)$storage[$k] = $DB->ForSql($v);
                // Если текущего склада от моста нет в индексе - добавляем в 
                // промежуточную таблицу
                //============================================================
                //      Заполнение промежуточной таблицы новыми данными
                //============================================================
                if(!isset($indexStorages[$storage["option_id"]])){
                    $query = "
                        INSERT `int_storages_import`(
                            `external_id`,
                            `name`,
                            `schedule`,
                            `description`,
                            `address`
                        )
                        VALUES(
                            '".$storage["option_id"]."',
                            '".$storage["name"]."',
                            '".$storage["schedule"]."',
                            '".$storage["description"]." ".$storage["path"]."',
                            '".$storage["address"]."'
                        )
                    ";
                    $DB->Query($query);
                }
                // Если текущая категория от моста есть в индексе - обновляем в 
                // промежуточной таблице
                else{
                    $query = "
                        UPDATE 
                            `int_storages_import`
                        SET
                            `name`='".$storage["name"]."',
                            `schedule`='".$storage["schedule"]."',
                            `description`='".$storage["description"]." "
                                .$storage["path"]."',
                            `address`='".$storage["address"]."'
                        WHERE
                            `external_id`='".$storage["option_id"]."'
                    ";
                    $DB->Query($query);
                }
                
                //============================================================
                //      Заполнение битрикса данными
                //============================================================
                // Ищем в каталоге битрикса имя разжела с таким же именем
                CModule::IncludeModule("catalog");
                $res = CCatalogStore::GetList(
                    array(), array("TITLE"=>$storage["name"])
                );
                // Формируем поля раздела
                $arFields = array(
                    "TITLE"         =>  $storage["name"],
                    "DESCRIPTION"   =>  $storage["description"]." "
                        .$storage["path"],
                    "ADDRESS"       =>  $storage["address"],
                    "SCHEDULE"      =>  $storage["schedule"],
                );
                $row = $res->GetNext();
                // Если склад уже есть в битриксе - обновляем
                if($row){
                    
                    // Обновляем ращдел каталога
                    CCatalogStore::Update($row["ID"], $arFields);
                    
                    // Привязываем к объекту промежуточной таблицы ID склада в
                    // Битриксе
                    $query = "
                        UPDATE 
                            `int_storages_import` 
                        SET 
                            `bitrix_id`=".$row["ID"]." 
                        WHERE 
                            `external_id`=".$storage["option_id"];
                    $DB->Query($query);
                    
                }
                // Если раздела ещё нет в битриксе - добавляем
                else{
                    // Пытвемся добавить раздел в битрикс, при неудаче фиксируем 
                    // ошибку
                    if(!$storageId = CCatalogStore::Add($arFields))
                        $this->error = "Ошибка добавления склада";
                    
                    // Привязываем к объекту промежуточной таблицы ID раздела в
                    // Битриксе
                    $query = "
                        UPDATE 
                            `int_storages_import` 
                        SET 
                            `bitrix_id`=".$storageId." 
                        WHERE 
                            `external_id`=".$storage["option_id"];
                    $DB->Query($query);
                }
                
                
            }// END: Перебираем полученные от моста категории

            
        }
        
    }

