<?php


class Migration{
    
    protected $data = array();
    protected $error = '';
    
    function __construct(){
        // Подключение ядра 1С-Битрикс
        define ('NOT_CHECK_PERMISSIONS', true);
        define ('NO_AGENT_CHECK', true);
        $GLOBALS['DBType'] = 'mysql';
        $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..' );
        $_SERVER["HTTP_HOST"] = 'localhost';
        $_SERVER['REQUEST_URI'] = "/";
        require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

        // Искуственная авторизация в роли админа
        $user = new CUser;
        $user->Authorize(1);
        
    }
    
    /**
     * Накат миграции
     * возвращает true, если миграция прошла успешно
    */
    protected function Run(){
        return true;
    }

    /**
     * Откат миграции
    */
    protected function RollBack(){
        
    }
    
    /**
     * Останов миграции при ошибке
     */
    protected function FatalError($message = ''){
        echo $message;
        return false;
    }
    
}
