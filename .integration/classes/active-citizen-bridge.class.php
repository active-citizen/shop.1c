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
                ),
                "mode"=>"emp"
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
        );
        
        private $method = 'auth'; //!< Метод 
        private $mode = 'dummy';
        private $arguments = array();
        private $errors = array();
        
        
        function __construct(){
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
            $data = $methodObject->exec($this->arguments);
            if(!$data = json_decode($data)){
                $this->addError("Ошибка преобразования json к объекту");
                return false;
            }
            if(!$data = $this->objectToArray($data)){
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
        private function objectToArray($object){
            if(is_object($object))$object = get_object_vars($object);
            if(!is_array($object))return false;
            foreach($object as $key=>$value){
                if(is_object($value) || is_array($value))$object[$key] = $this->objectToArray($value);
            }
            return $object;
        }
        
        
        
    }
