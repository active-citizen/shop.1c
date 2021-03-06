<?php
    namespace Curl;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once("CCurlHeaders.class.php");
    use AGShop; 
   
    class CCurlSimple extends \AGShop\CAGShop{
        var $timeout = 20;
        
        function get($url, $headers = []){
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            if($headers)
               curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            return curl_exec ($ch);
            }

        function head($url,$headers){
            $tmpfilename = tempnam(sys_get_temp_dir(),'curl_');
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($ch, CURLOPT_NOBODY, true );
            curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);
            if($headers)
               curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
            $hd = fopen($tmpfilename,"w");
            curl_setopt ($ch, CURLOPT_WRITEHEADER, $hd);
            $result = curl_exec ($ch);
            fclose($hd);
            $headers = file($tmpfilename);
            $result = \Curl\CCurlHeader::headers2Array($headers);
            unlink($tmpfilename);
            return $result;
        }


        function post($url,$postData, $headers = array("Content-Type: application/json")){
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            return curl_exec ($ch);
            
        }
        
    }
