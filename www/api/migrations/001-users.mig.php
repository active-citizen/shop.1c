#!/usr/bin/php
<?php
    
    require(realpath(dirname(__FILE__).
        "/../include/classes/CMigration.class.php"));


    $objCMigration = new CMigration();

    // Таблица пользователей 
    $objCMigration->dropTable("users");

    // Таблица пользователей 
    $objCMigration->addTable("users");

    // Телефон
    $objCMigration->setColumn(
        "users",  // Table Name
        "phone",       // Column name
        "BIGINT",      // Column type
        11,         // Size
        false,          // Default
        true,
        'Номер телефона в формате 79171234567',// Comment
        false      // IS NULL
    );
 
    // Ключ для телефона
    $objCMigration->setkey(
        "users",     // Table name
        "phone",   //
        array("phone"),    // Fields
        true           // Unique
    );

    // md5 телефонпароль 
    $objCMigration->setColumn(
        "users",  // table name
        "password",       // column name
        "char",      // column type
        32,         // size
        false,          // default
        false,
        'md5 конкатенции телефон+пароль',// comment
        false      // is null
    );

    // Ключ для пароля
    $objCMigration->setkey(
        "users",     // Table name
        "password",   //
        array("password"),    // Fields
        false           // Unique
    );

    // SSO_ID
    $objCMigration->setColumn(
        "users",  // Table Name
        "sso_id",   // Column name
        "CHAR",      // Column type
        40,         // Size
        false,          // Default
        false,
        'sso_id пользователя',// Comment
        false      // IS NULL
    );
 
    // Ключ для sso_id 
    $objCMigration->setkey(
        "users",     // Table name
        "sso_id",   //
        array("sso_id"),    // Fields
        true           // Unique
    );

    // Баланс
    $objCMigration->setColumn(
        "users",  // Table Name
        "balance",   // Column name
        "FLOAT",      // Column type
        "10,2",         // Size
        false,          // Default
        false,
        'Баланс пользователя',// Comment
        false      // IS NULL
    );

    $objCMigration->setColumn(
        "users",  // Table Name
        "all_points",   // Column name
        "FLOAT",      // Column type
        "10,2",         // Size
        false,          // Default
        false,
        '',// Comment
        false      // IS NULL
    );

    $objCMigration->setColumn(
        "users",  // Table Name
        "current_points",   // Column name
        "FLOAT",      // Column type
        "10,2",         // Size
        false,          // Default
        false,
        '',// Comment
        false      // IS NULL
    );


    $objCMigration->setColumn(
        "users",  // Table Name
        "spent_points",   // Column name
        "FLOAT",      // Column type
        "10,2",         // Size
        false,          // Default
        false,
        '',// Comment
        false      // IS NULL
    );

    $objCMigration->setColumn(
        "users",  // Table Name
        "freezed_points",   // Column name
        "FLOAT",      // Column type
        "10,2",         // Size
        false,          // Default
        false,
        '',// Comment
        false      // IS NULL
    );
///////////////////////////////////////////////////////////////////////////////

    // Таблица пользователей 
    $objCMigration->dropTable("profiles");

    // Таблица пользователей 
    $objCMigration->addTable("profiles");

    // ID пользователя
    $objCMigration->setColumn(
        "profiles",  // Table Name
        "user_id",   // Column name
        "INT",      // Column type
        11,         // Size
        false,         // Default
        true,
        'ID пользователя',// Comment
        false
    );

    // Ключ для ID пользователя
    $objCMigration->setkey(
        "profiles",     // table name
        "user_id",   //
        array("user_id"),    // fields
        false           // unique
    );
    
    // ID пользователя
    $objCMigration->setColumn(
        "profiles",  // Table Name
        "json_data",   // Column name
        "LONGTEXT",      // Column type
        false,         // Size
        false,         // Default
        false,
        'JSON профиля',// Comment
        false
    );
    


