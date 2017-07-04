<?php

if(!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
    $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../");

require_once(
    $_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php"
        );

require_once(realpath(dirname(__FILE__))."/migration.class.php");


class Migration{
    
    protected $data = array();
    protected $error = '';
    
    function __construct(){
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
