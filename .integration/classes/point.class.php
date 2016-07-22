<?
/*
 * point.class.php
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


    class bxPoint{
        
        /**
         * Обновление в битриксе транзакций личного счёта из занных EМП
         */
        function updatePoints($history, $userId){
            $history = array_reverse($history);
            
            CModule::IncludeModule("sale");
            
            
            $objTransact = new CSaleUserTransact;

            $res = $objTransact->GetList();
//            echo "<pre>";
//            print_r($res->GetNext());
//            die;

            for($i=0;$i<10;$i++){
                $empTransact = $history[0];
                $arFields = array(
                    "USER_ID"       =>  $userId,
                    "AMOUNT"        =>  $empTransact['points'],
                    "CURRENCY"      =>  "BAL",
                    "DEBIT"         =>  ($empTransact['action']=='debit'?'Y':'N'),
                    "DESCRIPTION"   =>  $empTransact["title"],
                    "ORDER_ID"      =>  "",
                    "EMPLOYEE_ID"   =>  1,
                    "TRANSACT_DATE" =>  date("d.m.Y H:i:s", $empTransact["date"])
                );                
                
                if(!$transactId = $objTransact->Add($arFields)){
                    echo "<pre>";
                    print_r($objTransact);
                    die;
                }
                echo "<pre>";
                print_r($arFields);
                die;
                
                
            }
            
            
        }
    }
