<?php
/*
 * curl.class.php
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

    class curlTool{
        
        var $timeout = 15;
        
        function get($url){
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            return curl_exec ($ch);
        }

        function head($url){
            $tmpfilename = tempnam(sys_get_temp_dir(),'curl_');
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($ch, CURLOPT_NOBODY, true );
            $hd = fopen($tmpfilename,"w");
            curl_setopt ($ch, CURLOPT_WRITEHEADER, $hd);
            $result = curl_exec ($ch);
            fclose($hd);
            $headers = file($tmpfilename);
            $result = array();
            foreach($headers as $header){
                if(preg_match("#^(.*?):(.*)$#",$header,$m))
                    $result[strtolower(trim($m[1]))] = trim($m[2]);
            }
            unlink($tmpfilename);
            return $result;
        }


        function post($url,$postData, $headers = array("Content-Type: application/json")){
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            return curl_exec ($ch);
            
        }

    }
