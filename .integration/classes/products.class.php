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
            
            $products = $this->getProducts();
            
            // Перебираем полученные от моста категории
            $resElement = new CIBlockElement;
            $counter = 0;
            foreach($products as $product){
                // Ищем в каталоге битрикса имя продукта с таким же именем
                $res = CIBlockElement::GetList(
                    array(), 
                    $arFields = array(
                        "IBLOCK_ID"=>$CatalogIblockId,"CODE"=>$product["CODE"]
                    )
                );
                $row = $res->GetNext();

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
                    
                    $picturePath = isset($product["IMAGES"][0])?
                        $product["IMAGES"][0]:'';
    
                    $product["PROPERTIES"]["MORE_PHOTO"] = array();
                    // Убираем дубли изображений
                    $images = array();
                    foreach($product["IMAGES"] as $image)$images[$image] = 1;
                    $product['images'] = array();
                    foreach($images as $image=>$v)
                        $product["PROPERTIES"]["MORE_PHOTO"][] = $image;
    
                    $product["PREVIEW_PICTURE"] = CFile::MakeFileArray($picturePath);
                    $product["DETAIL_PICTURE"] = CFile::MakeFileArray($picturePath);
                    unset($product["IMAGES"]);

                    if(!$id = $resElement->Add($product)){
                        $this->error = $resElement->LAST_ERROR;
                        echo "<pre>";
                        print_r($resElement);
                        print_r($product);
                        die;
                    }

                    foreach($product["PROPERTIES"] as $prop_code=>$prop_value){
                        if($prop_code=='MORE_PHOTO' && is_array($prop_value)){
                            $arrFile = array();
                            foreach($prop_value as $img)
                                $arrFile[] = array(
                                    "VALUE"=>CFile::MakeFileArray($img),
                                    "DESCRIPTION"=>""
                                );
                            
                            CIBlockElement::SetPropertyValuesEx(
                                $id, $CatalogIblockId, 
                                array('MORE_PHOTO' => $arrFile)
                            );
                        }
                        else{
                            CIBlockElement::SetPropertyValueCode(
                                $id,$prop_code,$prop_value
                            );
                        }
                    }


                    foreach($product["OFFERS"] as $offer){
                        $offerFields = array(
                            "IBLOCK_ID"         =>  $OfferIblockId,
                            "NAME"              =>  (isset($offer["NAME"]) && $offer["NAME"]?$offer["NAME"]:$product["NAME"]),
                            "PRICE"             =>  (isset($offer["PRICE"]) && $offer["PRICE"]?$offer["PRICE"]:$product["PROPERTIES"]["MINIMUM_PRICE"]),
                            "DETAIL_TEXT"       =>  $product["DETAIL_TEXT"],
                            "PREVIEW_TEXT"      =>  $product["PREVIEW_TEXT"],
                            "PREVIEW_TEXT_TYPE" =>  $product["PREVIEW_TEXT_TYPE"],
                            "PREVIEW_PICTURE"   =>  (isset($offer["PREVIEW_PICTURE"]) && $offer["PREVIEW_PICTURE"]? CFile::MakeFileArray($offer["PREVIEW_PICTURE"]):$product["PREVIEW_PICTURE"]),
                            "DETAIL_PICTURE"    =>  (isset($offer["DETAIL_PICTURE"]) && $offer["DETAIL_PICTURE"]? CFile::MakeFileArray($offer["DETAIL_PICTURE"]):$product["DETAIL_PICTURE"]),
                        );
                        
                        
                        if(!isset($offer["PROPERTIES"]) || !is_array($offer["PROPERTIES"]))$offer["PROPERTIES"] = array();
                        if(!$offerId = $resElement->Add($offerFields)){
                            echo "Error!!! ".__LINE__." ".print_r($resElement);
                            die;
                        }
                        
                        
                        $offer["PROPERTIES"]["CML2_LINK"] = $id;
                        $offer["PROPERTIES"]["PRICE"] = $offerFields["PRICE"];
                        $offer["PROPERTIES"]["MORE_PHOTO"] = $product["PROPERTIES"]["MORE_PHOTO"];
                        foreach($offer["PROPERTIES"] as $prop_code=>$prop_value){
                            if($prop_code=='MORE_PHOTO' && is_array($prop_value)){
                                $arrFile = array();
                                foreach($prop_value as $img)
                                    $arrFile[] = array(
                                        "VALUE"=>CFile::MakeFileArray($img),
                                        "DESCRIPTION"=>""
                                    );
                                
                                CIBlockElement::SetPropertyValuesEx(
                                    $offerId, 
                                    $OfferIblockId, 
                                    array('MORE_PHOTO' => $arrFile)
                                );
                            }
                            elseif($prop_code=='PRICE' && !is_array($prop_value)){
                                $arFields = array(
                                    "PRODUCT_ID"=>$offerId,
                                    "CATALOG_GROUP_ID"=>1,
                                    "PRICE"=>$prop_value,
                                    "CURRENCY"=>"BAL",
                                );
                                $objPrice = new CPrice;
                                if(!$priceId = $objPrice->Add($arFields,true)){
                                    echo "Error!!!: ".__LINE__." ".print_r($objPrice,1);;
                                    die;
                                }
                            }
                            elseif($prop_code=='MORE_PHOTO' && !is_array($prop_value)){
                                $prop_value = CFile::MakeFileArray($prop_value);
                                CIBlockElement::SetPropertyValueCode($offerId,$prop_code,$prop_value);
                            }
                            elseif($prop_code=='CML2_LINK' && is_array($prop_value)){
                                if(!CIBlockElement::SetPropertyValueCode($offerId,$prop_code,$prop_value)){
                                    echo "Failed";
                                }
                                else{
                                    echo "Success";
                                }
                            }
                            else{
                                CIBlockElement::SetPropertyValueCode($offerId,$prop_code,$prop_value);
                            }
                        }

                        $resCatalogStoreProduct = new CCatalogStoreProduct;
                        $totalAmount = 0;
                        foreach($offer["STORES"] as $storeId=>$storeAmount){
                            $arFields = array(
                                "PRODUCT_ID"=>$offerId,
                                "STORE_ID"=>$storeId,
                                "AMOUNT"=>$storeAmount
                            );
                            if(!$resCatalogStoreProduct->Add($arFields)){
                                echo "Error!!!: ".__LINE__;
                                print_r($resCatalogStoreProduct);
                                die;
                            }
                            $totalAmount+=$storeAmount;
                        }
    
                        CCatalogProduct::Add(array(
                            "ID"=>$offerId,
                            "QUANTITY"=>$totalAmount,
                            "QUANTITY_TRACE"=>"Y",
                            "CAN_BUY_ZERO"=>"N",
                        ));

                    }
                }
                
                $counter++;
                if($counter>10)break;
                
            }// END: Перебираем полученные от моста категории

            
        }
        
        
        private function getProducts(){
            
            global $DB;
            
            CModule::IncludeModule("iblock");
            // Узнаём ID инфоблока каталога
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
            $iblock = $res->GetNext();
            $CatalogIblockId = $iblock["ID"];

        
            // Составляем справочник флагов
            $ENUM = array();
            $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>$CatalogIblockId));
            while($data = $res->getNext()){
                $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
                if(!isset($ENUM[$data["PROPERTY_CODE"]]))$ENUM[$data["PROPERTY_CODE"]] = array();
                $ENUM[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
            }
            
            // Составляем справочник производителей
            $MANUFACTURERS = array();
            $query = "SELECT * FROM `int_manufacturers_import`";
            $res = $DB->Query($query);
            while($row = $res->GetNext())
                $MANUFACTURERS[$row["external_id"]] = $row;
                
            // Составляем справочник категорий
            $CATS = array();
            $query = "SELECT * FROM `int_categories_import`";
            $res = $DB->Query($query);
            while($row = $res->GetNext())
                $CATS[$row["external_id"]] = $row;

            // Составляем справочник складов
            $STORAGES = array();
            $query = "SELECT * FROM `int_storages_import`";
            $res = $DB->Query($query);
            while($row = $res->GetNext())
                $STORAGES[$row["external_id"]] = $row;
                
            
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
            
            $result = array();

            foreach($products as $productItem){
                //============================================================
                //      Заполнение битрикса данными
                //============================================================
                
                //----------------- Формируем поля продукта --------------------
                $product = array();
                $product["SITE_ID"] = 's1';
                $product["NAME"] = html_entity_decode($productItem["name"]);
                $product["CODE"] = Cutil::translit($productItem["name"],"ru",
                    array("replace_space"=>"-","replace_other"=>"-")
                );
                $product["IBLOCK_ID"] = $CatalogIblockId;
                $product["DETAIL_TEXT"] = html_entity_decode(
                    $productItem["description"]
                );
                $product["PREVIEW_TEXT"] = html_entity_decode(
                    $productItem["description"]
                );
                $product["PROPERTIES"] = array();
                $product["PROPERTIES"]["CANCEL_ABILITY"] = 
                    $productItem["allow_return"]?
                    $ENUM["CANCEL_ABILITY"]["Да"]:
                    0;
                $product["PROPERTIES"]["MINIMUM_PRICE"] = $productItem["price"];
                $product["PROPERTIES"]["QUANT"] = $productItem["unit"];
                $product["PROPERTIES"]["SALELEADER"] = $productItem["sale"];
                $product["PROPERTIES"]["RATING"] = round($productItem["rank"]/100,3);
                
                // ............Заданные случайно
                
                $product["PROPERTIES"]["TYPES"] = 
                    $ENUM["TYPES"][array_rand($ENUM["TYPES"])];
                $product["PROPERTIES"]["WANTS"] = 
                    $ENUM["WANTS"][array_rand($ENUM["WANTS"])];
                $product["PROPERTIES"]["INTERESTS"] = 
                    $ENUM["INTERESTS"][array_rand($ENUM["INTERESTS"])];
                $product["PROPERTIES"]["NEWPRODUCT"] = 
                    rand()>0.5?
                    $ENUM["NEWPRODUCT"]["да"]:
                    0;
                $product["PROPERTIES"]["SPECIALOFFER"] = 
                    rand()>0.5?
                    $ENUM["SPECIALOFFER"]["да"]:
                    0;
                // ............END: Заданные случайно
                
                
                
                // .......... Определяем параметры производителя..........
                $manufacturer = $MANUFACTURERS[$productItem["manufacturer_id"]];
                $manufacturerCode = '<table class="manufacturer">';
                if($manufacturer["name"])
                    $manufacturerCode .='<tr class="name"><th>Поставщик</th>'
                    .'<td>'.$manufacturer["name"].'</td></tr>';
                if($manufacturer["address"])
                    $manufacturerCode .='<tr class="address"><th>Адрес</th>'
                    .'<td>'.$manufacturer["address"].'</td></tr>';
                if($manufacturer["path"])
                    $manufacturerCode .='<tr class="path"><th>Как проехать</th>'
                    .'<td>'.$manufacturer["path"].'</td></tr>';
                if($manufacturer["schedule"])
                    $manufacturerCode .='<tr class="schedule"><th>График работы</th>'
                    .'<td>'.$manufacturer["schedule"].'</td></tr>';
                if($manufacturer["phone"])
                    $manufacturerCode .='<tr class="phone"><th>Телефон</th>'
                    .'<td>'.$manufacturer["phone"].'</td></tr>';
                if($manufacturer["email"])
                    $manufacturerCode .='<tr class="email"><th>Email</th>'
                    .'<td>'.$manufacturer["email"].'</td></tr>';
                if($manufacturer["url"])
                    $manufacturerCode .='<tr class="url"><th>Сайт</th>'
                    .'<td><a target="_blank" href="'.$manufacturer["url"].'">'.
                    $manufacturer["url"].'</a></td></tr>';
                if($manufacturer["description"])
                    $manufacturerCode .='<tr class="description"><th>Описание</th>'
                    .'<td>'.$manufacturer["description"].'</td></tr>';
                $manufacturerCode .= '</table>';
                $product["PROPERTIES"]["MANUFACTURER"] = $manufacturerCode;
                // .......END: Определяем параметры производителя.........
                
                $sectionId = isset($CATS[$productItem["categories"][0]]["bitrix_id"])
                    ?$CATS[$productItem["categories"][0]]["bitrix_id"]:0;
                    
                $product["IBLOCK_SECTION_ID"] = $sectionId;
                $product["SECTION_ID"] = $sectionId;
                $product["PREVIEW_TEXT_TYPE"] = 'html';
                $product["DETAIL_TEXT_TYPE"] = 'html';
                $product["IMAGES"] = $productItem["images"];
                
                //..............Предложения..................
                $product["OFFERS"] = array(
                    array(
                        "PROPERTIES"=>array(
                            "ARTNUMBER"=>md5(rand()),
                        ),
                        "STORES"=>array()
                    )
                );
                
                foreach($productItem["options"] as $option)
                    $product["OFFERS"][0]["STORES"][$STORAGES[$option["id"]]["bitrix_id"]] = 
                        $option["quantity"];
                
                //...........END:Предложения.................
                
                //-------------- END: Формируем поля продукта ------------------
                $result[] = $product;
            }

            return $result;
        }
        
    }

