<?
    namespace AGShop;
    //require_once(realpath(__DIR__."/../..")."/vendor/autoload.php");
    
    class CAGShop{


        private $arErrors = []; //!< Массив последних ошибок

        function __construct(){
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
