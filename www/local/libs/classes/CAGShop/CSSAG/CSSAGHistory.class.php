<?php
    namespace SSAG;
    require_once(realpath(__DIR__)."/CSSAG.class.php");
    use AGShop as AGShop;
    use AGShop\SSAG as SSAG;



    /**
        Класс для работой с историей транзакций СС АГ
    */
    class CSSAGHistory extends CSSAG{
        
        function __construct($sSessionId = '',$nUserId = 0){
            parent::__construct($sSessionId);
        }

        /**
            Получение истории начисления/списания
        */
        function get(
            $nPage = 1,
            $bDebit = null,
            $nOnPage = 20
        ){
            $this->setHTTPMethod("GET");
            $this->setMethod("/mvag/billing/getHistory");
            $this->setParam('page',$nPage);
            if(!is_null($bDebit))
                $this->setParam('debit',$bDebit);
            $this->setParam('onpage',$nOnPage);

            if(!$this->request()){
                $this->addError('Ошибка запроса к ССАГ');
            }

            return $arResult;
        }
        
    }
   
