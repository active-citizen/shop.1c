<?
    require_once(realpath(dirname(__FILE__))."/integrations.class.php");

    class CParking extends CIntegration{
   
        function __construct($sPhone){
            parent::__construct();
            if($this->error)return false;
            if(!$this->checkPhone($sPhone))return false;
            
        }

    }
    
