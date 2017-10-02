<?
    require_once(realpath(dirname(__FILE__)."/../")."/CMethod.class.php");
    require_once(realpath(dirname(__FILE__)."/../")."/CApplication.class.php");
    require_once(realpath(dirname(__FILE__)."/../")."/CTransaction.class.php");
    require_once(realpath(dirname(__FILE__)."/../")."/CSession.class.php");
    require_once(realpath(dirname(__FILE__)."/../")."/CUser.class.php");
    class CMethod_transaction extends CMethod{

        const ERROR_TRANSACTION_IS_EMPTY = 201;         // Пустой аргумент
        const ERROR_TRANSACTION_APP_ID_EMPTY = 202;     // Не указано ID приложения
        const ERROR_TRANSACTION_APP_ID_UNDEFINED = 203; // Неизвестный ID приложения
        const ERROR_TRANSACTION_POINTS_INCORRECT = 204; // Некорректное число баллов
        const ERROR_TRANSACTION_DEBIT_INCORRECT = 205;  // Некорректное дебет
        const ERROR_TRANSACTION_COMMENT_EMPTY = 206;     // Не указан комментарий
       
        function __construct($sLang = 'en', $sSessionId=''){
            parent::__construct("",$sLang, $sSessionId);
            return true;
        }

        function argsError($args = null){
            $CApplication = new CApplication;
            
            if(!$args)
                return self::$langErrors[CMethod_transaction::ERROR_TRANSACTION_IS_EMPTY];
            if(!property_exists($args,"appId"))
                return self::$langErrors[CMethod_transaction::ERROR_TRANSACTION_APP_ID_EMPTY];
            if(!property_exists($args,"comment") || !$args->comment)
                return self::$langErrors[CMethod_transaction::ERROR_TRANSACTION_COMMENT_EMPTY];
            if(!$CApplication->get($args->appId))
                return self::$langErrors[CMethod_transaction::ERROR_TRANSACTION_APP_ID_UNDEFINED];
            if(!property_exists($args,"points") || !floatval($args->points))
                return self::$langErrors[CMethod_transaction::ERROR_TRANSACTION_POINTS_INCORRECT];
            if(!property_exists($args,"debit") || !floatval($args->debit))
                return self::$langErrors[CMethod_transaction::ERROR_TRANSACTION_DEBIT_INCORRECT];
        }

        function go($args=''){
            // Получаем SSO_ID пользователя, который залогинен от этой сессии

            $CTransaction = new CTransaction;

            if(!$CTransaction->addEMPPoints(
                $this->session_id,
                $args->debit,
                $args->points,
                $args->comment
            )){
                return false;
            }

/*
 *              FIXME собственный метод добавления
                $this->session_id,
                $args->appId,
                $args->debit,
                $args->points,
                $args->comment
*/
            
            return array("session_id"=>$this->session_id);
        }
    }
