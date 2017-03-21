<?
/*
 * enc_auth.class.php
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

    require_once(realpath(dirname(__FILE__)."/../../curl.class.php"));
    
    class enc_authBridgeMethod{
        function exec($args,$contour='uat'){
            require($secretFilename = realpath(dirname(__FILE__)."/../../../secret.inc.php"));
            
            // Алгоритм шифрования
            $method = "rijndael-128";
            // Метод
            $mode = 'cbc';
            // Ключ шифрования
            $key = $AG_KEYS[CONTOUR]["key"];
            // Зашифрованная сессия из скрипта
            $encData = $args["session_id"];
            
        
            $module = mcrypt_module_open($method,'',$mode,'');
        
            $key = substr(md5($key),0,mcrypt_get_key_size($module));
            $encData = base64_decode($encData);
        
            $ivSize = mcrypt_enc_get_iv_size($module);
            // Выделяем IV из зашифрованной строки
            //$iv=hex2bin(substr(bin2hex($encData),0,2*$ivSize));
            $iv=mb_substr($encData,0,$ivSize,'8bit');
            
            mcrypt_generic_init($module, $key, $iv);
            
            // Расшифровываем строку без IV
            $decrypted=mdecrypt_generic($module,mb_substr($encData,$ivSize,mb_strlen($encData,'8bit'),'8bit'));
            
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
            $session_id = rtrim($decrypted,"\0");
            
            $args["token"] = $EMP_TOKENS[CONTOUR];
                
            $data = array(
                "token"=>$args["token"],
                "auth"=>array(
                    "session_id"=> $session_id
                )
            );
            
            $data = json_encode($data);
            $curl = new curlTool;
            $data = $curl->post("https://emp.mos.ru/v2.0.0/agprofile/getProfile", $data, array("Content-Type: application/json"));
            return $data;
        }
    }

