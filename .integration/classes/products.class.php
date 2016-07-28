<?
/*
 * products.class.php
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

    class bxProducts{
        
        var $errors = array();
        var $logs = array();
        
        /*
         * Заполнение промежуточной таблицы данными товаров из внешнего 
         * источника
         * @param - $updatePeriod - период обновления товаров
         *      (0 - принудительное обновление)
         */
        function updateImportTable($updatePeriod = 3600){
            
            global $DB;

            CModule::IncludeModule("iblock");
            // Узнаём ID инфоблока товарных предложений
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"));
            $iblock = $res->GetNext();
            $OfferIblockId = $iblock["ID"];
            
            // Узнаём ID инфоблока каталога
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
            $iblock = $res->GetNext();
            $CatalogIblockId = $iblock["ID"];

        
        
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
                        `int_products_import` 
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
            $agBrige->setMethod('getProducts');
            // Режим моста
            $agBrige->setMode('arm');
            // Аргументы
            $agBrige->setArguments($args);
            // Ошибки, возникшие во время установки параметров моста
            $this->errors = $agBrige->getErrors();
            
            // Если ошибок нет - выполняем установленным метод моста
            if(!$this->errors && !$products = $agBrige->exec()){
                $this->errors = array_merge(
                    $this->errors, $agBrige->getErrors()
                );
            }
            
            // Перебираем полученные от моста категории
            $resElement = new CIBlockElement;
            echo "<pre>";
            print_r($products);
            die;
            foreach($products as $productItem){
                //============================================================
                //      Заполнение битрикса данными
                //============================================================
                // Ищем в каталоге битрикса имя продукта с таким же именем
                $res = CIBlockSection::GetList(
                    array(), array("NAME"=>$productItem["name"])
                );
                $row = $res->GetNext();
                
                // Формируем поля продукта
                $product = array();
                $product["SITE_ID"] = 's1';
                $product["IBLOCK_ID"] = $CatalogIblockId;
                $product["DETAIL_TEXT"] = html_entity_decode($productItem["description"]);
                $product["PREVIEW_TEXT"] = html_entity_decode($productItem["description"]);
                
                
                // Определяем Bitrix-ID категории
                $res = $DB->Query("
                    SELECT 
                        `bitrix_id` 
                    FROM
                        `int_categories_import` 
                    WHERE 
                        `external_id`=".
                            (
                                isset($productItem["categories"][0])
                                ?
                                $productItem["categories"][0]
                                :
                                0
                            )
                            ."
                    LIMIT 1
                ");
                $arrSection = $res->GetNext();
                $sectionId = isset($arrSection["bitrix_id"])
                    ?
                    $arrSection["bitrix_id"]
                    :
                    0;
                $product["IBLOCK_SECTION_ID"] = $sectionId;
                $product["SECTION_ID"] = $sectionId;
                $product["PREVIEW_TEXT_TYPE"] = 'html';
                $product["DETAIL_TEXT_TYPE"] = 'html';
                $picturePath = isset($productItem["images"][0])
                    ?
                    $productItem["images"][0]
                    :
                    '';
                $product["PREVIEW_PICTURE"] = CFile::MakeFileArray($picturePath);
                $product["DETAIL_PICTURE"] = CFile::MakeFileArray($picturePath);

                echo "<pre>222";
                print_r($product);
                die;
                
                // Если раздел уже есть в битриксе - обновляем
                if($row){
                    continue;
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
                    if(!$id = $resElement->Add($product)){
                        print_r($resSection);
                        continue;
                    }
                echo "<pre>111";
                print_r($product);
                die;
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

