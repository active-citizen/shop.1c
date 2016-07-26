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
        
        var $error;
        
        /*
         * Заполнение промежуточной таблицы данными категорий из внешнего источника
         * @param - $updatePeriod - период обновления категорий (0 - принудительное обновление)
         */
        function updateImportTable($updatePeriod = 3600){
            
            global $DB;
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
            
            if(!$needUpdate)return false;
            
            $agBrige = new ActiveCitizenBridge;
            $answer = array(
                "errors"=>""
            );
            
            $args = array();
            
            $agBrige->setMethod('getCategories');
            $agBrige->setMode('arm');
            $agBrige->setArguments($args);
            $answer["errors"] = $agBrige->getErrors();
            
            if(!$answer["errors"] && !$categories = $agBrige->exec()){
                $answer["errors"] = array_merge($answer["errors"],$agBrige->getErrors());
            }

            // Составляем индекс текущего содержимого промежуточной таблицы
            // Ключ - ID внешнего источника данных
            $indexCategories = array();
            $query = "SELECT * FROM `int_categories_import`";
            $res = $DB->Query($query);
            while($row = $res->GetNext())$indexCategories[$row["external_id"]] = $row;
            
            foreach($categories as $category){
                foreach($category as $k=>$v)$category[$k] = $DB->ForSql($v);
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
                
                
                $res = CIBlockSection::GetList(array(),array("NAME"=>$category["name"]));
                $row = $res->GetNext();
                // Если раздел уже есть - обновляем, иначе добавляем в битрикс
                $arFields = array(
                    "IBLOCK_ID"         =>  $iblockId,
                    "NAME"              =>  $category["name"],
                    "CODE"              =>  CUtil::translit($category["name"],'ru',array("replace_space"=>"-","replace_other"=>"-")),
                    "SORT"              =>  $category["sort_order"],
                    "DESCRIPTION"       =>  $category["description"],
                    "DESCRIPTION_TYPE"  =>  'html'
                );
                if($row){
                    
                    $pictureId = $row["PICTURE"];
                    // Если размер файла не изменился - оставляем старый, иначе удаляем и заменяем новым
                    $newFileArray = CFile::MakeFileArray($category["image"]);
                    $res = CFile::GetByID($pictureId);
                    $oldFileArray = $res->GetNext();
                    
                    if($oldFileArray["FILE_SIZE"]!=$newFileArray['size']){
                        CFile::Delete($pictureId);
                        $arFields["PICTURE"] = $newFileArray;
                    }
                    
                    $objIblockSection->Update($row["ID"], $arFields);
                    
                    $query = "UPDATE `int_categories_import` SET `bitrix_id`=".$row["ID"]." WHERE `external_id`=".$category["category_id"];
                    $DB->Query($query);
                    
                }
                else{
                    $arFields["PICTURE"] = CFile::MakeFileArray($category["image"]);
                    if(!$categoryId = $objIblockSection->Add($arFields)){
                        $this->error = $objIblockSection->LAST_ERROR;
                    }
                    $query = "UPDATE `int_categories_import` SET `bitrix_id`=".$categoryId." WHERE `external_id`=".$category["category_id"];
                    $DB->Query($query);
                }
                
                
            }
        }
        
    }

