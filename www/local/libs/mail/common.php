<?php

    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");

    define("LOCAL_MAIL_SMTP_ENABLE",true);
    define("LOCAL_MAIL_SMPT_LOG_ENABLE",true);
    
    define("LOCAL_MAIL_SMTP_LOG_BASEDIR",
        realpath($_SERVER["DOCUMENT_ROOT"]."/../logs/smtplog/")
    );
    
    define("LOCAL_MAIL_SMTP_QUEUE",
        realpath($_SERVER["DOCUMENT_ROOT"]."/../mail_queue/")
    );

    // Сколько писем слать за раз
    define("LOCAL_MAIL_SMTP_QUANT", 30);

    define("LOCAL_MAIL_SMTP_HOST",$MAIL["smtp.host"]);
    define("LOCAL_MAIL_SMTP_PORT",$MAIL["smtp.port"]);
    define("LOCAL_MAIL_SMTP_AUTH",true);
    define("LOCAL_MAIL_SMTP_USER",$MAIL["smtp.user"]);
    define("LOCAL_MAIL_SMTP_PASS",$MAIL["smtp.password"]);
    define("LOCAL_MAIL_SMTP_SECU",$MAIL["smtp.encrypt"]);
    define("LOCAL_MAIL_SMTP_FROM",$MAIL["smtp.from"]);
    define("LOCAL_MAIL_SMTP_FROM_NAME","Магазин поощрений АГ");


    define("LOCAL_MAIL_DISK_ENABLE",true);
    define("LOCAL_MAIL_DISK_BASEDIR", 
        realpath($_SERVER["DOCUMENT_ROOT"]."/../logs/maildir/")
    );

    define("MAIL_ROOT_DIR",realpath(dirname(__FILE__)));

    $DISKMAIL_FILENAME = MAIL_ROOT_DIR."/disk_mailer.php";
    $SMTPMAIL_FILENAME = MAIL_ROOT_DIR."/smtp_mailer.php";
   
    if(file_exists($DISKMAIL_FILENAME) && file_exists($SMTPMAIL_FILENAME)){
        require($DISKMAIL_FILENAME);
        require($SMTPMAIL_FILENAME);
    }
