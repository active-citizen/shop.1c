<?php


class Migration{
    
    protected $data = array();
    
    function __construct(){
        // Подключение ядра 1С-Битрикс
        define ('NOT_CHECK_PERMISSIONS', true);
        define ('NO_AGENT_CHECK', true);
        $GLOBALS['DBType'] = 'mysql';
        $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..' );
        $_SERVER['REQUEST_URI'] = "/";
        require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

        // Искуственная авторизация в роли админа
        $_SESSION['SESS_AUTH']['USER_ID'] = 1;
        
    }
    
    /**
     * Накат миграции
    */
    protected function Go(){
    }

    /**
     * Откат миграции
    */
    protected function RollBack(){
    }
    
    protected function FatalError($message){
    }
    
}