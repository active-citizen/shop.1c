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
            
            require_once(realpath(dirname(__FILE__)."/../../../secret.inc.php"));
            
            
            $session_id = unpack("H*",mcrypt_decrypt(
                    MCRYPT_3DES, 
                    pack("H*", $AG_KEYS[$contour]["key"]), 
                    base64_decode($args["session_id"]),
                    MCRYPT_MODE_ECB
                ));

            $session_id = 
                is_array($session_id) && isset($session_id[1])
                ?$session_id[1]
                :'';

            
            if($contour=='uat')
                $args["token"] = $EMP_TOKENS["uat"];
            else
                $args["token"] = $EMP_TOKENS["prod"];
                
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

