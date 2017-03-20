#!/usr/bin/php
<?php
    
    require(realpath(dirname(__FILE__).
        "/../include/classes/CMigration.class.php"));


    $objCMigration = new CMigration();
    // Таблица сессий

    // Таблица пользователей 
    // символы суффикса - последние 2 цифры сессии 
    $objCMigration->dropTable(
        "sessions",    // Table Name
        true,       // Multiply 
        2,          // Digits of prefix 
        16,         // Base of digit        
        "x"         // Format (decimal)
    );
    
    $objCMigration->addTable("sessions",true,2,16,"x");

    // ID сессии
    $objCMigration->setColumn(
        "sessions",  // Table Name
        "session_id",       // Column name
        "CHAR",      // Column type
        32,         // Size
        false,          // Default
        false,
        'ID сессии',// Comment
        false,      // IS NULL
        true,       // Multiply table
        2,          // Myltiply suffix digits
        16,         // suffix digits base
        'x'         // Suffix digits format
    );
   

    // Пользователь, которому принадлежит сессия
    $objCMigration->setColumn(
        "sessions", // Table Name
        "user_id",  // Column name
        "INT",      // Column type
        11,         // Size
        0,          // Default
        true,
        'ID пользователя сессии',// Comment
        false,      // IS NULL
        true,       // Multiply table
        2,          // Myltiply suffix digits
        16,         // suffix digits base
        'x'         // Suffix digits format
    );

    // Ключ для ID сессии
    $objCMigration->setkey(
        "sessions",     // Table name
        "session_id",   //
        array("session_id"),    // Fields
        true,           // Unique
        true,           // Multiply table
        2,              // Myltiply suffix digits
        16,             // suffix digits base
        'x'             // Suffix digits format
    );

    // Ключ для ID пользователя
    $objCMigration->setkey(
        "sessions",     // table name
        "user_id",   //
        array("user_id"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        16,             // suffix digits base
        'x'             // suffix digits format
    );

    // Ключ для исключение дубля добавления 
    $objCMigration->setkey(
        "sessions",        // table name
        "user_session",   //
        array("user_id","session_id"),    // fields
        true,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        16,             // suffix digits base
        'x'             // suffix digits format
    );

