<?php
    require_once(realpath(dirname(__FILE__))."/CAll.class.php");
    require_once(realpath(dirname(__FILE__))."/CSession.class.php");
    require_once(realpath(dirname(__FILE__))."/curl.class.php");

    define("METHOD_DIR",realpath(dirname(__FILE__)."/methods/")."/");

    class CMethod extends CAll{

        protected $session_id = '';

        function __construct($sMethodName='',$sLang = 'en', $sSessionId = ''){
            
            parent::__construct($sLang);
            $this->session_id = $sSessionId;

            if(!trim($sMethodName))return true;

            if(!$sMethodClassName = self::exists($sMethodName)){
                $this->setError(CAll::ERROR_METHOD_NOT_EXISTS);
                return false;
            }
            
            return new $sMethodClassName();
        }

        function argsError($args = null){
            return true;
        }

        static function exists($sMethodName=''){
            $sMethodFilename = METHOD_DIR.$sMethodName.".method.php";
            if(!file_exists($sMethodFilename))return false;
            require_once($sMethodFilename);

            if(!class_exists(
                $sClassName = self::getMethodClassName($sMethodName)
            ))return false;

            return $sClassName;
        }

        static private function getMethodClassName($sMethodName){
            return "CMethod_".$sMethodName;
        }
        
        protected function getUser(){
            $CSession = new CSession;
            return $CSession->get($this->session_id);
        }
    }
