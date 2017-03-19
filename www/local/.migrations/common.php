<?php


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
