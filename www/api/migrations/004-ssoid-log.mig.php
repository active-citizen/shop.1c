#!/usr/bin/php
<?php
    
    require(realpath(dirname(__FILE__).
        "/../include/classes/CMigration.class.php"));


    $objCMigration = new CMigration();

    // Таблица логов SSO-ID 
    $objCMigration->dropTable("ssoid_log");

    // Таблица пользователей 
    $objCMigration->addTable("ssoid_log");

    $objCMigration->setColumn(
        "ssoid_log",  // Table Name
        "session_id",       // Column name
        "CHAR",      // Column type
        32,         // Size
        false,          // Default
        false,
        'ID сессии',// Comment
        false      // IS NULL
    );
 
    $objCMigration->setColumn(
        "ssoid_log",  // table name
        "sso_id",       // column name
        "CHAR",      // column type
        40,         // size
        false,          // default
        false,
        'SSO ID',// comment
        false      // is null
    );



