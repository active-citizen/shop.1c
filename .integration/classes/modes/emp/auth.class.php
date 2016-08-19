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
            if($contour=='uat')
                $args["token"] = "ag_uat_token3";
            else
                $args["token"] = "ag_token";
                
            $data = array(
                "token"=>$args["token"],
                "auth"=>array(
                    "login"     =>  $args["login"],
                    "password"  => $args["password"]
                )
            );
            
            $data = json_encode($data);
            $curl = new curlTool;
            $data = $curl->post("https://emp.mos.ru/v2.0.0/agprofile/getProfile", $data, array("Content-Type: application/json"));
            return $data;
        }
    }

