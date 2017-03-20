<?
    require_once(realpath(dirname(__FILE__)."/../")."/CMethod.class.php");
    require_once(realpath(dirname(__FILE__)."/../")."/CApplication.class.php");
    require_once(realpath(dirname(__FILE__)."/../")."/CTransaction.class.php");
    class CMethod_points extends CMethod{

        const ERROR_POINTS_IS_EMPTY         = 301; // Пустой аргумент
        const ERROR_POINTS_APP_ID_UNDEFINED = 303; // Неизвестный ID приложения
       
        function __construct($sLang = 'en',$sSessionId=''){
            parent::__construct("", $sLang, $sSessionId);
            return true;
        }

        function argsError($args = null, $session_id=''){
            $CApplication = new CApplication;
            
            if(!$args)
                return self::$langErrors[CMethod_points::ERROR_POINTS_IS_EMPTY];
            if(property_exists($args,"appId") && !$CApplication->get($args->appId))
                return self::$langErrors[CMethod_points::ERROR_POINTS_APP_ID_UNDEFINED];
        }

        function go($args=''){
            $CTransaction = new CTransaction;
            $result = $CTransaction->getPoints(
                $this->session_id,
                property_exists($args, "appId") && $args->appId?$args->appId:false, 
                property_exists($args, "debet") && $args->debet?$args->debet:false,
                property_exists($args, "from") && $args->from?intval($args->from):false,
                property_exists($args, "to") && $args->to?intval($args->to):false,
                property_exists($args, "accepted") && $args->accepted?$args->accepted:false
            );
            return array(
                "history"=>$result
            );
        }
    }
