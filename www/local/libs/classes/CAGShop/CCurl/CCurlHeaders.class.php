<?php
    namespace Curl;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGShop; 
   
    class CCurlHeaders extends \AGShop\CAGShop{
        var $timeout = 20;

        static function headers2Array($headers){
            $result = array();
            foreach($headers as $header){
                if(preg_match("#^(.*?):(.*)$#",$header,$m))
                    $result[strtolower(trim($m[1]))] = trim($m[2]);
            }
            return $result;
        }
    }
