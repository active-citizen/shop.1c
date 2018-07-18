<?php
    namespace Phone;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    
    use AGShop;
    use AGShop\DB as DB;
    
    class CPhone extends \AGShop\CAGShop{
        function __construct(){
            parent::__construct();
        }
        
        function isCorrect($sPhoneNumber){
            return boolval(preg_match("#^\d{5,11}$#",$sPhoneNumber));
        }
    }
    
