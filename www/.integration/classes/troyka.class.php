<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    class CTroyka{
        var $number = false;
        var $error = '';
        function __construct($nNum){

            CModule::IncludeModule("sale");

            if(!preg_match("#^\d{10}$#",$nNum)){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Некорректный номер тройки";
                return false;
            }

            return true;
        }

        function linkOrder(
            $nOrderId      // ID заказа
        ){
              
            if(!preg_match("#^\d+$#",$nOrderId)){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Некорректный номер заказа";
                return false;
            }

            
        }

    }

?>
