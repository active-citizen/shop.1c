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
        
        /**
         * Обновление в битриксе транзакций личного счёта из занных EМП
         */
        function updateOrders($orders, $userId){
            global $USER;
            global $DB;
            $objBridge = new ActiveCitizenBridge();
            // Получаем список статусов
            $statusesList = $this->getStatuses();
            
            CModule::IncludeModule("sale");
            CModule::IncludeModule("catalog");
            CModule::IncludeModule("iblock");
            // Узнаём ID инфоблока товарных предложений
            $res = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"));
            $iblock = $res->GetNext();
            $OfferIblockId = $iblock["ID"];

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
            foreach($orders as $order){
                // Формируем массив лдя вствавки
                $person_type_id = 1;
                $payed = "N";
                $canceled = "N";
                
                $bxStatus = $statusesList[$order["order_status_id"]]['bitrix_id'];
                if($order["order_status_id"]==7)$canceled = "Y";
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
                if($this->getOrderById($order["order_id"])){
                    continue;
                }

                $this->addOrder($order["order_id"],$order);

                // Добавляем заказ
                $objOrder = new CSaleOrder;
                if(!$bxOrderId = $objOrder->Add($arFields)){
                    print_r($objOrder);
                    $error = $objOrder->LAST_ERROR;
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
                
                foreach($order["products"] as $product){
                    $arFields = array(
                        "NAME"      =>  $product["name"],
                        "IBLOCK_ID" =>  $OfferIblockId
                    );
                    $res =  CIBlockElement::GetList(
                        array(),
                        $arFields,
                        false,
                        array("nTopCount"=>1)
                    ); 
                    $arOffer = $res->GetNext();
                    echo "<pre>";
                    print_r($arOffer);
                    echo "</pre>";
                }
                
                
                
                echo "<pre>";
                print_r($order["products"][0]);
                die;
            }
            
            die;
            
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
        
        
    }
