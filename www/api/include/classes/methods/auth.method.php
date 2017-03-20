<?
    require_once(realpath(dirname(__FILE__)."/../")."/CMethod.class.php");
    class CMethod_auth extends CMethod{

        const ERROR_AUTH_EMP_CONNECTION  = 101;  // Ошибка соединения с ЕМП
        const ERROR_AUTH_JSON_PARSE      = 102;  // Ошибка парсинга ответа
        const ERROR_AUTH_NO_SESSION_ID   = 103;  // Ошибка получения сессии
        const ERROR_AUTH_NO_PROFILE_RESULT = 104;    // Вернулся пустой профиль
        const ERROR_AUTH_EMP_NO_SSOID = 105;     // Нет SSOID
        
        const ERROR_AUTH_IS_EMPTY = 106;     // Пустой запрос
        const ERROR_AUTH_LOGIN_IS_NOT_EXISTS = 107;     // Нет логина
        const ERROR_AUTH_PASSWORD_IS_NOT_EXISTS = 108;     // Нет Пароля

       
        function __construct($sLang = 'en', $sSessionId = ''){
            parent::__construct("", $sLang, $sSessionId);
            self::setErrorLanguage($sLang);
            return true;
        }

        function argsError($args = null,$sLang='en'){
            if(!$args)
                return self::$langErrors[CMethod_auth::ERROR_AUTH_IS_EMPTY];
            if(!property_exists($args,"login"))
                return self::$langErrors[CMethod_auth::ERROR_AUTH_LOGIN_IS_NOT_EXISTS]; 
            if(!property_exists($args,"password"))
                return self::$langErrors[CMethod_auth::ERROR_AUTH_PASSWORD_IS_NOT_EXISTS]; 
        }

        function go($args=''){
            $data = array(
                "token"=>$GLOBALS["CONF"]["mvag_token"],
                "auth"=>array(
                    "login"     =>  $args->login,
                    "password"  =>  $args->password
                )
            );
            $data = json_encode($data);
            $curl = new curlTool;
            $data = $curl->post(
                "https://emp.mos.ru/v2.0.0/agprofile/getProfile", 
                $data, 
                array("Content-Type: application/json")
            );
            if(!$data){
                self::setError(CMethod_auth::ERROR_AUTH_EMP_CONNECTION); 
                return false;
            }
            if(!$data=json_decode($data)){
                self::setError(CMethod_auth::ERROR_AUTH_JSON_PARSE); 
                return false;
            }
            
            if(
                !property_exists($data, "session_id")
                ||
                !$data->session_id
            ){
                self::setError(CMethod_auth::ERROR_AUTH_NO_SESSION_ID); 
                return false;
            }
            
            require_once(realpath(dirname(__FILE__)."/../")."/CUser.class.php");
            require_once(realpath(dirname(__FILE__)."/../")."/CSession.class.php");
            $CUser = new CUser();
            $CSession = new CSession();
            
            if(!$CSession->get($data->session_id)){
                if(
                    !property_exists($data, "result")
                    ||
                    !$data->result
                ){
                    self::setError(CMethod_auth::ERROR_AUTH_NO_PROFILE_RESULT); 
                    return false;
                }
                $objEMPProfile = $CUser->getEMPProfile($data->session_id);
                
                if(
                    !property_exists($objEMPProfile->result, "ssoId")
                    ||
                    !$objEMPProfile->result->ssoId
                ){
                    self::setError(CMethod_auth::ERROR_AUTH_EMP_NO_SSOID); 
                    return false;
                }
                
                if(!$arUser = $CUser->getBySSOID($objEMPProfile->result->ssoId)){
                    if(!$nUserId = $CUser->Add(array(
                        "phone" =>$objEMPProfile->result->msisdn,
                        "sso_id"=>$objEMPProfile->result->ssoId
                    ),$data->result)){
                        return false;
                    }
                }
                else{
                    $nUserId = $arUser["id"];
                }
                $CSession->add($data->session_id, $nUserId);
            }
            
            
            
            return array("session_id"=>$data->session_id);
        }
    }
