<?
/*
 * categories.class.php
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

    class bxManufacturers{
        
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
                        `int_manufacturers_import` 
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
            $agBrige->setMethod('getManufacturers');
            // Режим моста
            $agBrige->setMode('arm');
            // Аргументы
            $agBrige->setArguments($args);
            // Ошибки, возникшие во время установки параметров моста
            $this->errors = $agBrige->getErrors();
            
            // Если ошибок нет - выполняем установленным метод моста
            if(!$this->errors && !$manufacturers = $agBrige->exec()){
                $this->errors = array_merge(
                    $this->errors, $agBrige->getErrors()
                );
            }

            // Перебираем полученные от моста категории
            foreach($manufacturers as $manufacturer){
                // Подготавливаем все параметры категории для работы с SQL
                foreach($manufacturer as $k=>$v)$manufacturer[$k] = $DB->ForSql($v);
                //============================================================
                //      Заполнение промежуточной таблицы новыми данными
                //============================================================
                // Если текущей категории от моста нет в таблице - добавляем в 
                // промежуточную таблицу
                $query = "
                    SELECT 
                        * 
                    FROM 
                        `int_manufacturers_import` 
                    WHERE
                        `external_id`=".$manufacturer["manufacturer_id"]."
                    LIMIT
                        1
                ";

                $res = $DB->Query($query);
                if(!$row=$res->GetNext()){
                    $query = "
                        INSERT `int_manufacturers_import`(
                            `external_id`,
                            `name`,
                            `image`,
                            `description`,
                            `schedule`,
                            `path`,
                            `address`,
                            `url`,
                            `phone`,
                            `email`,
                            `shortname`
                        )
                        VALUES(
                            '".$manufacturer["manufacturer_id"]."',
                            '".$manufacturer["name"]."',
                            '".$manufacturer["image"]."',
                            '".$manufacturer["description"]."',
                            '".$manufacturer["schedule"]."',
                            '".$manufacturer["path"]."',
                            '".$manufacturer["address"]."',
                            '".$manufacturer["url"]."',
                            '".$manufacturer["phone"]."',
                            '".$manufacturer["email"]."',
                            '".$manufacturer["shortname"]."'
                        )
                    ";
                    $DB->Query($query);
                }
                // Если текущий производитель от моста есть в индексе - обновляем в 
                // промежуточной таблице
                else{
                    $query = "
                        UPDATE 
                            `int_manufacturers_import`
                        SET
                            `name`='".$manufacturer["name"]."',
                            `image`='".$manufacturer["image"]."',
                            `description`='".$manufacturer["description"]."',
                            `schedule`='".$manufacturer["schedule"]."',
                            `path`='".$manufacturer["path"]."',
                            `address`='".$manufacturer["address"]."',
                            `url`='".$manufacturer["url"]."',
                            `phone`='".$manufacturer["phone"]."',
                            `email`='".$manufacturer["email"]."',
                            `shortname`='".$manufacturer["shortname"]."'
                        WHERE
                            `external_id`='".$manufacturer["manufacturer_id"]."'
                    ";
                    $DB->Query($query);
                }
                
                
            }// END: Перебираем полученные от моста категории

            
        }
        
    }

