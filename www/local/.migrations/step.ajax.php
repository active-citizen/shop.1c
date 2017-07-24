<?
    define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php"
    );
    require_once('common.php');
    if(!$USER->isAdmin()){
        echo "Access denied";
        exit();
    }
    require_once('migration.class.php');

    $filename = isset($_POST["filename"]) ?  $_POST["filename"] : "" ;
    if(
        !preg_match("#^[\.]?[\d\w\-]+/[\d\.]+\-[\-\w\d]+\.mig$#",$filename)
        &&
        !preg_match("#^[\d\.]+\-[\-\w\d]+\.mig$#",$filename)
    ){
        echo "Migration filename invalid";
        exit();
    }

    $sIncludeFilename = "migs/$filename";
    if(!file_exists($sIncludeFilename)){
        echo "Migration file not exists";
        exit();
    }
    
    include($sIncludeFilename);


