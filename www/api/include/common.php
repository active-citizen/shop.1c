<?php

    $_SERVER["HTTP_HOST"] = 'bcc-dev.ag.mos.ru';
    
    if(isset($_SERVER["HTTP_HOST"])){
    }
    elseif(isset($argv[1])){
        $_SERVER["HTTP_HOST"] = $argv[1];
    }
    else{
        echo "\nHTTP_HOST undefined\n";
        die;
    }

    define("INC_PATH",realpath(dirname(__FILE__))."/");
    // Подключение общего конфига
    if(!file_exists(INC_PATH."config.php")){
        echo "Config file not found. Create include/config.php from include/config.tmpl.php";
        die;
    }
    else{
        require_once(INC_PATH."config.php");
    }
    
    // Подключаем суперкласс 
    require_once(INC_PATH."classes/CAll.class.php");
    // Устанавливаем язык
    CAll::setErrorLanguage('en');
    // Подключаем сессии
    require_once(INC_PATH."classes/CSession.class.php");

    // Подключаем контроллер методов 
    require_once(INC_PATH."classes/CMethod.class.php");

    // Соединяемся с БД
    require_once(INC_PATH."classes/wirix/db.class.php");

    $GLOBALS["CONF"] = array();
    foreach($conf as $sServerName=>$arServerConf)
        if($sServerName==$_SERVER["HTTP_HOST"])
            $GLOBALS["CONF"] = $arServerConf;

    if(!isset($GLOBALS["CONF"]["db_host"]))die("Database host undefined");
    if(!isset($GLOBALS["CONF"]["db_user"]))die("Database user undefined");
    if(!isset($GLOBALS["CONF"]["db_name"]))die("Database name undefined");
    if(!isset($GLOBALS["CONF"]["db_pass"]))die("Database password undefined");


    // Подключаемся к БД
    $GLOBALS["DB"] = new \wirix\db(
        $GLOBALS["CONF"]["db_host"],
        $GLOBALS["CONF"]["db_user"],
        $GLOBALS["CONF"]["db_pass"],
        $GLOBALS["CONF"]["db_name"]
    );

    /**
        Функция отладочной печати
    */
    function yprint($var,$stop_after_print = 0){
        // Класс отладочной печати
        require_once(INC_PATH."classes/Xprint.class.php");
        new XPrint($var,$stop_after_print);
    }
