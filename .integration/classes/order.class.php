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


    class bxOrder{

        var $error = '';
        
        /**
         * Обновление в битриксе транзакций личного счёта из занных EМП
         */
        function updateOrders($orders, $userId){
            
            global $USER;
            
            foreach($orders as $order){
                
                // Если заказ уже добавлен - пропускаем
                if($this->getOrderById($order["order_id"]))continue;
                
                $person_type_id = 1;
                $payed = "Y";
                
                
                $arFields = array(
                   "LID" => "ru",
                   "PERSON_TYPE_ID" => 1,
                   "PAYED" => $payed,
                   "CANCELED" => "N",
                   "STATUS_ID" => "N",
                   "PRICE" => 279.32,
                   "CURRENCY" => "USD",
                   "USER_ID" => intval($USER->GetID()),
                   "PAY_SYSTEM_ID" => 3,
                   "PRICE_DELIVERY" => 11.37,
                   "DELIVERY_ID" => 2,
                   "DISCOUNT_VALUE" => 1.5,
                   "TAX_VALUE" => 0.0,
                   "USER_DESCRIPTION" => ""
                );                
                            
                echo "<pre>";
                print_r($order);
                echo "</pre>";
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

            $res = $DB->Query("
                SELECT 
                    * 
                FROM 
                    `int_orders_import` 
                WHERE 
                    `external_id` = $order_id
                LIMIT
                    1
            ");
            
            return $res->GetNext();
        }
        
    }
