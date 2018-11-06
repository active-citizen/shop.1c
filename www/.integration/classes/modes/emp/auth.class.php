<?
/*
 * auth.class.php
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
    
    class authBridgeMethod{
        function exec($args,$contour='uat'){
            
            require(realpath(dirname(__FILE__)."/../../../secret.inc.php"));
            
            $data = array(
                "token"=>$EMP_TOKENS[CONTOUR],
                "auth"=>array(
                    "session_id"     =>  $args["session_id"],
                )
            );
           
            mkdir($_SERVER["DOCUMENT_ROOT"]."/../logs/mobile_emp/",0755,1);
            $fd = fopen($_SERVER["DOCUMENT_ROOT"]."/../logs/mobile_emp/".date("Y-m-d").".txt","a");
            fwrite($fd,"\n".date("Y-m-d H:i:s")." https://emp.mos.ru/v2.0.0/agprofile/getProfile");
            $data = json_encode($data);
            fwrite($fd," ".$data);
            $curl = new curlTool;
            $data = $curl->post(
                "https://emp.mos.ru/v2.0.0/agprofile/getProfile",
                $data, 
                array(
                    "Content-Type: application/json"
                )
            );
            fwrite($fd," ".$data);
            fclose($fd);
            return $data;
        }
    }

