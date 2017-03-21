<?
    define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
    require(
        $_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php"
    );
    require('common.php');
    if(!$USER->isAdmin()){
        echo "Access denied";
        exit();
    }

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


