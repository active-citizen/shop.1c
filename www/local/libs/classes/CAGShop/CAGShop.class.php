<?
    namespace AGShop;
    //require_once(realpath(__DIR__."/../..")."/vendor/autoload.php");
    
    class CAGShop{


        private $arErrors = []; //!< Массив последних ошибок
        var $IBLOCKS = [];
        var $PROPERTIES = [];

        function __construct(){
            if(defined("CATALOG_IB_ID"))
                $this->IBLOCKS["CATALOG"] = CATALOG_IB_ID;
            if(defined("OFFER_IB_ID"))
                $this->IBLOCKS["OFFER"] = OFFER_IB_ID;
            if(defined("WISHES_IB_ID"))
                $this->IBLOCKS["WISHES"] = WISHES_IB_ID;
                
            if(defined("INTEREST_PROPERTY_ID"))
                $this->PROPERTIES["INTEREST"] = INTEREST_PROPERTY_ID;
            if(defined("CML2_LINK_PROPERTY_ID"))
                $this->PROPERTIES["CML2_LINK"] = CML2_LINK_PROPERTY_ID;
            
        }


        function addError($error){
            if(is_array($error)){  
                foreach($error as $err)$this->arErrors[] = $err;
            }
            else{
                $this->arErrors[] = $error;
            }
        }

        function getErrors(){
            return $this->arErrors;
        }

        function clearError(){
            $this->arErrors = [];
        }
    }
