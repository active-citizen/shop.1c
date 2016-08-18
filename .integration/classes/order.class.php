<?
/*
 * order.class.php
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

    require_once("active-citizen-bridge.class.php");

    class bxOrder{

        var $error = '';
        var $max_creater_orders =3; 
        
        /**
         * Обновление в битриксе транзакций личного счёта из занных EМП
         */
        function updateOrders($orders, $userId){
            global $USER;
            global $DB;
            $objBridge = new ActiveCitizenBridge();
            // Получаем список статусов
            $statusesList = $this->getStatuses();
            $result = array();
            
            CModule::IncludeModule("sale");
            CModule::IncludeModule("catalog");
            CModule::IncludeModule("iblock");
            CModule::IncludeModule("price");
            // Узнаём ID инфоблока товарных предложений
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"));
            $iblock = $res->GetNext();
            $OfferIblockId = $iblock["ID"];

            // Узнаём ID инфоблока каталога
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
            $iblock = $res->GetNext();
            $CatalogIblockId = $iblock["ID"];

            $objOrder = new CSaleOrder;
            $objBasket = new CSaleBasket;

            // Определяем ID платёжной системы
            $res= CSalePaySystem::GetList(array(),array("ACTIVE"=>"Y"));
            $paySystem = $res->GetNext();
            $paySystemId = 9;
            if(isset($paySystem["ID"]))$paySystemId = $paySystem["ID"];
            
            // Определяем ID системы доставки
            $res = CSaleDelivery::GetList(array(),array("ACTIVE"=>"Y"));
            $deliverySystem = $res->GetNext();
            $deliverySystemId = 3;
            if(isset($deliverySystem["ID"]))
                $deliverySystemId = $deliverySystem["ID"];
            // Перебираем все заказы, пришедшие извне
            $count = 0;
            foreach($orders as $order){

                die;

                $count++;
                if($count>$this->max_creater_orders)break;


                $arrProducts = array();
                // Собираем массив товаров заказа
                foreach($order["products"] as $product){
                    if(!$bxProduct = $this->getBxProductByExternalId(
                        $product["product_id"], $CatalogIblockId, $OfferIblockId
                    )){
                        $bxProduct = $this->addBxProduct(
                            $product,
                            $CatalogIblockId,
                            $OfferIblockId
                        );
                    }
                    $arrProducts[$bxProduct["ID"]] = 1;
                }
                
                // Формируем массив лдя вствавки
                $person_type_id = 1;
                $payed = "N";
                $canceled = "N";
                
                $bxStatus = $statusesList[$order["order_status_id"]]['bitrix_id'];
                if($order["order_status_id"]==7)$canceled = "Y";
                
                // Формируем поля заказа
                $arFields = array(
                   "LID"                =>  "s1",
                   "PERSON_TYPE_ID"     =>  $person_type_id,
                   "PAYED"              =>  $payed,
                   "CANCELED"           =>  $canceled,
                   "STATUS_ID"          =>  $bxStatus,
                   "PRICE"              =>  $order['total'],
                   "CURRENCY"           =>  "BAL",
                   "USER_ID"            =>  intval($USER->GetID()),
                   "PAY_SYSTEM_ID"      =>  $paySystemId,
                   "PRICE_DELIVERY"     =>  0,
                   "DELIVERY_ID"        =>  $deliverySystemId,
                   "DISCOUNT_VALUE"     =>  0,
                   "TAX_VALUE"          =>  0,
                   "USER_DESCRIPTION"   =>  $order["comment"],
                   "DATE_INSERT"        =>  $DB->FormatDate(
                                                $order["date_added"],
                                                "Y-m-d H:i:s"
                                            ),
                   "DATE_UPDATE"        =>  $DB->FormatDate(
                                                $order["date_modified"],
                                                "Y-m-d H:i:s"
                                            )
                );      

                // Если заказ уже добавлен - обновляем его статус
                if($orderInfo = $this->getOrderById($order["order_id"])){
                    $objOrder->Update(
                        $orderInfo["bitrix_id"],
                        $arFields
                    );
                    
                    $resBasket =  $objBasket->GetList(
                        array(),array("ORDER_ID"=>$orderInfo["bitrix_id"])
                    );
                    
                    $indexBasket = array();
                    while($itemBasket = $resBasket->GetNext())
                        $indexBasket[$itemBasket["PRODUCT_ID"]] = $itemBasket;
                    
                    $userBasketId = $objBasket->GetBasketUserID();
                    
                    foreach($arrProducts as $productId=>$item){
                        if(!$indexBasket[$productId])
                            if(!$basketItemId = $objBasket->Add($addArrBasket = 
                                array(
                                    "PRODUCT_ID"        =>  $productId,
                                    "PRICE"             =>  $product["price"],
                                    "CURRENCY"          =>  "BAL",
                                    "QUANTITY"          =>  1,
                                    "LID"               =>  LANG,
                                    "DELAY"             =>  "N",
                                    "CAN_BUY" => "Y",
                                    "MODULE"            => "catalog",
                                    "NAME"              =>  $product["name"],
                                )
                            )){
                                echo $objBasket->LAST_ERROR;
                                echo "<pre>";
                                print_r($addArrBasket);
                                die;
                            }
                    }
                    CSaleBasket::OrderBasket(
                        $orderInfo["bitrix_id"], $userBasketId
                    );
                    $result[] = array(
                        "order_id"=>
                    );
                    continue;
                }

                $this->addOrder($order["order_id"],$order);

                // Добавляем заказ
                if(!$bxOrderId = $objOrder->Add($arFields)){
                    $this->error = $objOrder->LAST_ERROR;
                    return false;
                }
                
                $query = "
                    UPDATE 
                        `int_orders_import`
                    SET 
                        `bitrix_id`=$bxOrderId
                    WHERE
                        `external_id`=".$order["order_id"]."
                    LIMIT
                        1
                ";
                
                $DB->Query($query);
                
                $userBasketId = $objBasket->GetBasketUserID();
                
                
                foreach($arrProducts as $productId=>$item){
                    if(!$basketItemId = $objBasket->Add($addArrBasket = 
                        array(
                            "PRODUCT_ID"        =>  $productId,
                            "PRICE"             =>  $product["price"],
                            "CURRENCY"          =>  "BAL",
                            "QUANTITY"          =>  1,
                            "LID"               =>  LANG,
                            "DELAY"             =>  "N",
                            "CAN_BUY" => "Y",
                            "MODULE"            => "catalog",
                            "NAME"              =>  $product["name"],
                        )
                    )){
                        echo $objBasket->LAST_ERROR;
                        echo "<pre>";
                        print_r($addArrBasket);
                        die;
                    }
                }
                CSaleBasket::OrderBasket($bxOrderId, $userBasketId);
            }
            
            return true;
            
        }
        
        /**
         * Получение записи-заказа из промежуточной таблицы по внешнему ID
         * 
         * @return массив записи промежуточной таблицы
        */
        function getOrderById($order_id){
            global $DB;

            $res = $DB->Query($query = "
                SELECT 
                    * 
                FROM 
                    `int_orders_import` 
                WHERE 
                    `external_id` = $order_id
                LIMIT
                    1
            ");
            
            $result = $res->GetNext();
            $objBridge = new ActiveCitizenBridge();
            if(isset($result["data"]))
                $result["data"] = $objBridge->objectToArray(json_decode(
                    $result["data"]
                ));
            unset($objBridge);

            return $result;
        }
        
        /**
         * Добавление в промежуточную таблицу информации о заказе
         * 
         * @return ID добавленной записи
        */
        function  addOrder(
            $external_id,   //!< ID заказа в arm или где ещё
            $data           //!< Массив заказа
        ){
            global $DB; 
            
            $data = json_encode($data);
            $query = "
                INSERT INTO `int_orders_import`(
                    `external_id`,
                    `data`
                )
                VALUES(
                    '".intval($external_id)."',
                    '".json_encode($data)."'
                )
            ";
            
            if($res = $DB->Query($query))return $DB->LastID();
            return false;
        }
        
        /*
         * Получение списка статусов
        */
        function getStatuses(){
            global $DB; 
            $res = $DB->Query("
                SELECT * FROM `int_status_import` ORDER BY external_id ASC
            ");
            $result = array();
            while($row = $res->GetNext())$result[$row["external_id"]] = $row;
            return $result;
        }
        
        
        /**
         * Получение продукта в битрикс по ID внешнего источника
         * @param $externalId - внешний ID
         * @param $CatalogIblockId - ID иефоблока с товарами
        */
        function getBxProductByExternalId(
            $externalId, $CatalogIblockId, $OfferIblockId
        ){
            
            $res = CIBlockElement::GetList(
                array(),
                array(
                    "IBLOCK_ID"             =>  $CatalogIblockId,
                    "PROPERTY_EXTERNAL_ID"  =>  $externalId
                ),
                false,
                array("nTopCount"=>1)
            );
            
            $product = $res->GetNext();
            if(!$product)return false;
            
            $res = CIBlockElement::GetList(
                array(),
                array(
                    "IBLOCK_ID"             =>  $OfferIblockId,
                    "PROPERTY_CML2_LINK"    =>  $product["ID"]
                ),
                false,
                array("nTopCount"=>1)
            );
            $offer = $res->GetNext();
            
            return $offer;
        }
        
        /**
         * Добавление
        */
        function addBxProduct(
            $product, $CatalogIblockId, 
            $OfferIblockId, $categoryId = 0
        ){
            $resElement = new CIBlockElement;
            $arrFields = array(
                "SITE_ID"       =>  "s1",
                "NAME"          =>  $product["name"],
                "CODE"          =>  Cutil::translit($product["name"],"ru",
                    array("replace_space"=>"-","replace_other"=>"-")
                ),
                "IBLOCK_ID"     =>  $CatalogIblockId,
                "DETAIL_TEXT"   =>  $product["model"],
                "PREVIEW_TEXT"  =>  $product["model"],
                "IBLOCK_SECTION_ID" =>  1,//$categoryId,
                "SECTION_ID"    =>  1,//$categoryId,
                "PREVIEW_TEXT_TYPE" =>  'html',
                "DETAIL_TEXT_TYPE"  =>  'html',
            );
            

            if(!$id = $resElement->Add($arrFields)){
                $this->error = $resElement->LAST_ERROR;
                echo $this->error;
                die;
                return false;
            }
            
            CIBlockElement::SetPropertyValueCode(
                $id,"EXTERNAL_ID",$product["product_id"]
            );
            
            $arrFields["IBLOCK_ID"] = $OfferIblockId;
            $arrFields["PRICE"] = $product["price"];
            if(!$offerId = $resElement->Add($arrFields)){
                $this->error = $resElement->LAST_ERROR;
                echo $this->error;
                die;
                return false;
            }
            
            $objPrice = new CPrice;
            $objPrice->Add(array(
                "PRODUCT_ID"=>$offerId,
                "CATALOG_GROUP_ID"=>1,
                "PRICE"=>$product["price"],
                "CURRENCY"=>"BAL",
            ),true);

            
            CIBlockElement::SetPropertyValueCode($offerId,"CML2_LINK",$id);

            CCatalogProduct::Add(array(
                "ID"=>$offerId,
                "QUANTITY"=>0,
                "QUANTITY_TRACE"=>"Y",
                "CAN_BUY_ZERO"=>"N",
            ));
            
            return array("ID"=>$offerId);
        }
        
    }
