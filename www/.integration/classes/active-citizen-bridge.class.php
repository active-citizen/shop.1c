<?php
/*
 * active-citizen-bridge.class.php
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


    /**
        Класс для интеграции с сервисами активного гражданина
    */
    class ActiveCitizenBridge{
        
        private $methods = array( //!< Методы и режимы их работы (dummy - пустой режим данные из файла)
            "auth"=> array(
                "name"      =>  "Авторизация и получение сессии",
                "inputs"    =>  array(
                    /*
                    "login" =>  array(
                        "name"      =>  "Логин(номер телефона)",
                        "require"   =>  true,
                        "regexp"    =>  "#^\d+$#"
                    ),
                    "password" =>  array(
                        "name"      =>  "Пароль",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,32}$#"
                    ),
                    */
                    "session_id" =>  array(
                        "name"      =>  "ID сессии",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,128}$#"
                    ),
                    "token" =>  array(
                        "name"      =>  "Токен",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,32}$#"
                    ),
                ),
                "mode"=>"emp"
            ),
            "enc_auth"=> array(
                "name"      =>  "Авторизация по кодированной сессии",
                "inputs"    =>  array(
                    "session_id" =>  array(
                        "name"      =>  "ID сессии",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,128}$#"
                    ),
                    "token" =>  array(
                        "name"      =>  "Токен",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,32}$#"
                    ),
                ),
                "mode"=>"emp"
            ),
            "pointsHistory"=> array(
                "name"      =>  "Получение истории начисления баллов",
                "inputs"    =>  array(
                    "session_id" =>  array(
                        "name"      =>  "ID сессии ЕМП-пользователя",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,64}$#"
                    ),
                    "token" =>  array(
                        "name"      =>  "Токен",
                        "require"   =>  true,
                        "regexp"    =>  "#^.{1,32}$#"
                    ),
                    "sync" =>   [
                        "name"=>"Синхронизация таблиц ККБ",
                        "require"=>false
                    ]
                ),
                "mode"=>"emp"
            ),
            "getOrders"=> array(
                "name"      =>  "Авторизация и получение сессии",
                "inputs"    =>  array(
                    "phone" =>  array(
                        "name"      =>  "Логин(номер телефона)",
                        "require"   =>  true,
                        "regexp"    =>  "#^\d+$#"
                    ),
                ),
                "mode"=>"arm"
            ),
            "getProducts"=> array(
                "name"      =>  "Получение товаров",
                "inputs"    =>  array(
                ),
                "mode"=>"arm"
            ),
            "getCategories"=> array(
                "name"      =>  "Получение категорий",
                "inputs"    =>  array(
                ),
                "mode"=>"arm"
            ),
            "getStorages"=> array(
                "name"      =>  "Получение складов",
                "inputs"    =>  array(
                ),
                "mode"=>"arm"
            ),
            "getManufacturers"=>array(
                "name"      =>  "Получение производителей",
                "inputs"    =>  array(
                ),
                "mode"=>"arm"
            ),
            "addOrder"=>array(
                "name"      =>  "Оформление заказа",
                "inputs"    =>  array(
                    "hash"=>array(
                        "name"      =>  "Ключ соединения, обязательный параметр",
                        "require"   =>  true,
                        "regexp"    =>  "#[\d\w]+#"
                    ),
                    "email"=>array(
                        "name"      =>  "E-mail, обязательный параметр",
                        "require"   =>  true,
                        "regexp"    =>  "#[\d\w\.\-\_]+\@[\d\w\.\-\_]+#"
                    ),
                    "firstname"=>array(
                        "name"      =>  "Имя, обязательный параметр",
                        "require"   =>  true,
                        "regexp"    =>  "#.+#"
                    ),
                    "lastname"=>array(
                        "name"      =>  "Фамилия, обязательный параметр",
                        "require"   =>  true,
                        "regexp"    =>  "#.+#"
                    ),
                    "telephone"=>array(
                        "name"      =>  "Телефон, обязательный параметр",
                        "require"   =>  true,
                        "regexp"    =>  "#\d{11}#"
                    ),
                    "secondname"=>array(
                        "name"      =>  "Отчество",
                        "require"   =>  false,
                        "regexp"    =>  "#.+#"
                    ),
                    "address"=>array(
                        "name"      =>  "Адрес",
                        "require"   =>  false,
                        "regexp"    =>  "#.+#"
                    ),
                    "postcode"=>array(
                        "name"      =>  "Почтовый индекс",
                        "require"   =>  false,
                        "regexp"    =>  "#\d{6}#"
                    ),
                    "shipping"=>array(
                        "name"      =>  "Тип доставки (web – по интернету, pickup - самовывоз) При отсутствии параметра выставляется по логике заданной [типом](types)",
                        "require"   =>  true,
                        "regexp"    =>  "#[\w\d]+#"
                    ),
                    "product_id"=>array(
                        "name"      =>  "ID товара",
                        "require"   =>  true,
                        "regexp"    =>  "#\d+#"
                    ),
                    "troyka_serial"=>array(
                        "name"      =>  "Номер карты Тройка (обязательный для заказа пополнения карты)",
                        "require"   =>  true,
                        "regexp"    =>  "#[\w\d]+#"
                    ),
                    "options"=>array(
                        "name"      =>  "Опции продукта(массив)",
                        "require"   =>  false,
                        "regexp"    =>  "#.*#"
                    ),
                    "lottery"=>array(
                        "name"      =>  "Заказ является выигрышем в лотерею(необязательный параметр)",
                        "require"   =>  false,
                        "regexp"    =>  "#.*#"
                    ),
                    
                ),
                "mode"=>"arm"
            )
        );
        
        private $method = 'auth'; //!< Метод 
        private $mode = 'dummy';
        private $arguments = array();
        private $errors = array();
        private $contour = 'uat';
        
        
        function __construct(){
            if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru')
                $this->contour = 'prod';
            if($_SERVER["HTTP_HOST"]=='dev.shop.ag.mos.ru')
                $this->contour = 'uat';
            if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru.local')
                $this->contour = 'test';
        }
        
        /**
         * Установка исполняемого метода 
         * 
         * @param $method - имя метода
         * @return true если без ошибок
         */
        function setMethod($method = 'auth'){
            if(!isset($this->methods[$method])){
                $this->addError("Метод \"$method\" не существует");
                return false;
            }
            $this->method = $method;
            return true;
        }

        /**
         * Установка режима исполняемого метода 
         * 
         * @param $mode - имя режима
         * @return true если без ошибок
         */
         function setMode($mode = ''){
             if(!$mode)return true;
             if(!$this->methods[$this->method]["mode"]){
                $this->addError("Метод \"$method\" не существует на момент установки режима");
                return false;
             }
             $this->mode = $mode; 
         }

        
        /**
         * Получение массива с возникшими ошибками
         * 
         * @return текст последней ошибки
         */
        function getErrors(){
            return $this->errors;
        }
        
        
        /**
         * Установка сообщения об ошибке
         * 
         * @param $errorText
         * @return true если без ошибок
         */
        private function addError($errorText = ''){
            $this->errors[] = $errorText;
        }
        
        /**
         * Установка аргументов исполняемого метода
         * 
         * @param $args - массив аргемунтов вида  array("аргумент"=>"значение")
         * @return true если без ошибок
         */
        function setArguments($args = array()){
             if(!isset($this->methods[$this->method])){
                 $this->addError("Метод \"$method\" не существует на момент установки аргументов");
                 return false;
             }
             
             
             foreach($this->methods[$this->method]["inputs"] as $argName=>$argInfo){
                if($argInfo["require"] && !isset($args[$argName])){
                     $this->addError("Аргумент $argName должен быть задан");
                     continue;
                }
                if($argInfo["regexp"] && !preg_match($argInfo["regexp"],$args[$argName])){
                     $this->addError("Аргумент \"$argName (".$argInfo["name"].")\" не соответствует шаблону \"".$argInfo["regexp"]."\"");
                     continue;
                }
                $this->arguments[$argName] = $args[$argName];
             }
        }
        
        
        /**
         * Выполнение установленного метода в установленном режиме с установленными аргументами
         * 
         * @return массив результата
         */
        function exec(){
            $currentDir = realpath(dirname(__FILE__));
            $modesDir = $currentDir."/modes/".$this->mode;
            $methodFile = $modesDir."/".$this->method.".class.php";
            $methodClass = $this->method."BridgeMethod";
            if(!file_exists($methodFile)){
                $this->addError("Файл \"$methodFile\" не найден");
                return false;
            }
            require_once($methodFile);
            if(!class_exists($methodClass)){
                $this->addError("Класс \"$methodClass\" в файле \"$methodFile\" не найден");
                return false;
            }
            $methodObject = new $methodClass;
            if(!method_exists($methodObject,"exec")){
                $this->addError("Метод \"exec\" в классе \"$methodClass\" не найден");
                return false;
            }
            $data = trim($methodObject->exec($this->arguments,$this->contour));
            $data = json_decode($data);
            // Занулили проверку
            if(0 && !is_object($data)){
                $this->addError("Ошибка преобразования json к объекту");
                return false;
            }
            $data = $this->objectToArray($data);
            // Занулили проверку
            if(0 && !$data = $this->objectToArray($data)){
                $this->addError("Ошибка преобразования json-объекта к массиву");
                return false;
            }
            return $data;
        }
        
        /*
         * 
         * Рекурсивная функция преобразования вложенного json-объекта ко вложенному массиву
         * 
         * @param $object - json-объект
         * @return vfccbd
         * 
         */
        function objectToArray($object){
            if(is_object($object))$object = get_object_vars($object);
            if(!is_array($object))return false;
            foreach($object as $key=>$value){
                if(is_object($value) || is_array($value))$object[$key] = $this->objectToArray($value);
            }
            return $object;
        }
        
        
        
    }
