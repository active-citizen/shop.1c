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

    class bxCategories{
        
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
            
            // Получаем ID IBlock-а под кодом 
            CModule::IncludeModule("iblock");
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
            $row = $res->GetNext();
            $iblockId = $row["ID"];
            $objIblockSection = new CIblockSection;
        
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
                        `int_categories_import` 
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
            $agBrige->setMethod('getCategories');
            // Режим моста
            $agBrige->setMode('arm');
            // Аргументы
            $agBrige->setArguments($args);
            // Ошибки, возникшие во время установки параметров моста
            $this->errors = $agBrige->getErrors();
            
            // Если ошибок нет - выполняем установленным метод моста
            if(!$this->errors && !$categories = $agBrige->exec()){
                $this->errors = array_merge(
                    $this->errors, $agBrige->getErrors()
                );
            }

            // Составляем индекс текущего содержимого промежуточной таблицы
            // Ключ - ID внешнего источника данных
            $indexCategories = array();
            $query = "SELECT * FROM `int_categories_import`";
            $res = $DB->Query($query);
            while($row = $res->GetNext())
                $indexCategories[$row["external_id"]] = $row;
            
            // Перебираем полученные от моста категории
            foreach($categories as $category){
                // Подготавливаем все параметры категории для работы с SQL
                foreach($category as $k=>$v)$category[$k] = $DB->ForSql($v);
                // Если текущей категории от моста нет в индексе - добавляем в 
                // промежуточную таблицу
                //============================================================
                //      Заполнение промежуточной таблицы новыми данными
                //============================================================
                if(!isset($indexCategories[$category["category_id"]])){
                    $query = "
                        INSERT `int_categories_import`(
                            `external_id`,
                            `name`,
                            `sort_order`,
                            `image`,
                            `external_parent_id`,
                            `description`
                        )
                        VALUES(
                            '".$category["category_id"]."',
                            '".$category["name"]."',
                            '".$category["sort_order"]."',
                            '".$category["image"]."',
                            '".$category["parent_id"]."',
                            '".$category["description"]."'
                        )
                    ";
                    $DB->Query($query);
                }
                // Если текущая категория от моста есть в индексе - обновляем в 
                // промежуточной таблице
                else{
                    $query = "
                        UPDATE 
                            `int_categories_import`
                        SET
                            `name`='".$category["name"]."',
                            `sort_order`='".$category["sort_order"]."',
                            `image`='".$category["image"]."',
                            `external_parent_id`='".$category["parent_id"]."',
                            `description`='".$category["description"]."'
                        WHERE
                            `external_id`='".$category["category_id"]."'
                    ";
                    $DB->Query($query);
                }
                
                
                //============================================================
                //      Заполнение битрикса данными
                //============================================================
                // Ищем в каталоге битрикса имя разжела с таким же именем
                $res = CIBlockSection::GetList(
                    array(), array("NAME"=>$category["name"])
                );
                $row = $res->GetNext();
                // Формируем поля раздела
                $arFields = array(
                    "IBLOCK_ID"         =>  $iblockId,
                    "NAME"              =>  $category["name"],
                    "CODE"              =>  CUtil::translit(
                        $category["name"], 'ru',
                        array("replace_space"=>"-","replace_other"=>"-")
                    ),
                    "SORT"              =>  $category["sort_order"],
                    "DESCRIPTION"       =>  $category["description"],
                    "DESCRIPTION_TYPE"  =>  'html'
                );
                // Если раздел уже есть в битриксе - обновляем
                if($row){
                    // Получаем ID ижображения для каталога
                    $pictureId = $row["PICTURE"];
                    // Загружаем изображение, указанное в атрибутах раздела,
                    // полученных из API (это url на внешний ресурс),
                    // И получаем массив его атрибутов (из него нам нужен размер)
                    $newFileArray = CFile::MakeFileArray($category["image"]);
                    // Получаем информацию о файле картинки каталога по его ID
                    // Нам нужен оттуда размер
                    $res = CFile::GetByID($pictureId);
                    $oldFileArray = $res->GetNext();
                    // Добавляем к полям раздела новую картинку, если размер
                    // загруженного извне изображения не совпадает с тем, что
                    // в битриксе
                    if($oldFileArray["FILE_SIZE"]!=$newFileArray['size']){
                        CFile::Delete($pictureId);
                        $arFields["PICTURE"] = $newFileArray;
                    }
                    
                    // Обновляем ращдел каталога
                    $objIblockSection->Update($row["ID"], $arFields);
                    
                    // Привязываем к объекту промежуточной таблицы ID раздела в
                    // Битриксе
                    $query = "
                        UPDATE 
                            `int_categories_import` 
                        SET 
                            `bitrix_id`=".$row["ID"]." 
                        WHERE 
                            `external_id`=".$category["category_id"];
                    $DB->Query($query);
                    
                }
                // Если раздела ещё нет в битриксе - добавляем
                else{
                    // Формируем массив добавляемого файла из ссылки на картинку
                    $arFields["PICTURE"] = CFile::MakeFileArray($category["image"]);
                    // Пытвемся добавить раздел в битрикс, при неудаче фиксируем 
                    // ошибку
                    if(!$categoryId = $objIblockSection->Add($arFields))
                        $this->error = $objIblockSection->LAST_ERROR;
                    
                    // Привязываем к объекту промежуточной таблицы ID раздела в
                    // Битриксе
                    $query = "
                        UPDATE 
                            `int_categories_import` 
                        SET 
                            `bitrix_id`=".$categoryId." 
                        WHERE 
                            `external_id`=".$category["category_id"];
                    $DB->Query($query);
                }
                
                
            }// END: Перебираем полученные от моста категории

            
        }
        
    }

