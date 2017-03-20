<?php
    require_once(realpath(dirname(__FILE__)."/../common.php"));

    require_once(realpath(dirname(__FILE__)."/wirix/db.class.php"));

    /**
     * Класс работы с приложениями
     */
    class CApplication{

        var $verbose = true;

        function __construct(){
        }
    
        /**
         * Получение информации о сессии
        */
        function get(
            $nAppId
        ){
            return $GLOBALS["DB"]->search_one(
                "applications",
                array("id"=>$nAppId),"","*"
            );
        }
        
    }
