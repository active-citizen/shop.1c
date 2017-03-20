<?
    class CAll{
        // Коды ошибок
        const ERROR_NO_REQUEST                      = 1; // Отсутствует запрос
        const ERROR_WRONG_REQUEST                   = 2; // Некорректный запрос
        const ERROR_NO_METHOD                       = 3; // Не указан метод
        const ERROR_METHOD_NOT_EXISTS               = 4; // Метод не существует   
        const ERROR_INIT_METHOD                     = 5; // Метод не существует   
        const ERROR_CHECK_ARGS                      = 6; // Метод не существует  
        const ERROR_EXECUTE_UNKNOWN                 = 7; // Неизвестная ошибка исполнения
        const ERROR_AUTH_ERROR                      = 8; // Ошибка авторизации
        const ERROR_SESSION_NOT_DEFINED             = 9;    // Не указана сессия
        const ERROR_SESSION_INCORRECT               = 10;   // Некорректный ID сессии

        static private $errno = '';
        static private $lang = 'en';
        static protected $langErrors = array();

        function __construct($sLang = 'en'){
            self::setErrorLanguage($sLang);
        }

        static function getErrorMessage($nErrorCode){
            if(isset(self::$langErrors[$nErrorCode]))
                return self::$langErrors[$nErrorCode];
            return "Unknown error code : ".$nErrorCode;
        }

        static function setError($errno){
            self::$errno = $errno;
        }
        
        function getErrorText(){
            return self::getErrorMessage($this->errno);
        }

        function getErrorNo(){
            return intval(self::$errno);
        }

        static function setErrorLanguage($sLang = 'en'){
            $sBaseDir = realpath(dirname(__FILE__)."/../lang/");
            $sLangFile = $sBaseDir."/".$sLang."/errors.php"; 
            if(!preg_match("#^\w{2}$#", $sLang) || !file_exists($sLangFile))
                return false;
            include($sLangFile);
            self::$langErrors = $ERR_MESS;
            self::$lang = $sLang;
            return true;
        }

        function getErrorLanguage(){
            return self::$lang;
        }
        
     }
