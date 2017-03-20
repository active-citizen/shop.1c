#!/usr/bin/php
<?php
    
    require(realpath(dirname(__FILE__).
        "/../include/classes/CMigration.class.php"));


    $objCMigration = new CMigration();


    $objCMigration->dropTable(
        "transacts_brief",    // Table Name
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Таблица краткой информации о транзекции
    // цифры суффикса - последние 2 цифры id пользователя 
    $objCMigration->addTable(
        "transacts_brief",    // Table Name
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // ID приложения
    $objCMigration->setColumn(
        "transacts_brief",  // Table Name
        "application_id",   // Column name
        "TINYINT",      // Column type
        3,         // Size
        false,         // Default
        true,
        'ID приложения',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Ключ для ID приложения
    $objCMigration->setkey(
        "transacts_brief",     // table name
        "application_id",   //
        array("application_id"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );

    // ID пользователя
    $objCMigration->setColumn(
        "transacts_brief",  // Table Name
        "user_id",   // Column name
        "INT",      // Column type
        11,         // Size
        false,         // Default
        true,
        'ID пользователя',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Ключ для ID пользователя
    $objCMigration->setkey(
        "transacts_brief",     // table name
        "user_id",   //
        array("user_id"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );


    // Дебит/кредит
    $objCMigration->setColumn(
        "transacts_brief",  // Table Name
        "debit",   // Column name
        "float",      // Column type
        "10,3",         // Size
        0,         // Default
        false,
        'Дебит',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Ключ для Дебет/кредит
    $objCMigration->setkey(
        "transacts_brief",     // table name
        "debit",   //
        array("debit"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );


    // Сумма транзакции
    $objCMigration->setColumn(
        "transacts_brief",  // Table Name
        "quantity",   // Column name
        "float",      // Column type
        "10,2",         // Size
        false,         // Default
        false,          // Is NULL
        'Сумма транзакции',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Транзакция принята
    $objCMigration->setColumn(
        "transacts_brief",  // Table Name
        "accepted",   // Column name
        "TINYINT",      // Column type
        "1",         // Size
        1,         // Default
        false,      // Unsigned
        'Транзакция принята',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Ключ для "Транзакция принята"
    $objCMigration->setkey(
        "transacts_brief",     // table name
        "accepted",   //
        array("accepted"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );


    // CRC32-хэш транзакции (userId.дата(timestamp).дебит(-1,1).сумма(float с 3 нолями после запятой).коммент(trim))
    $objCMigration->setColumn(
        "transacts_brief",  // Table Name
        "crc32",   // Column name
        "INT",      // Column type
        "11",         // Size
        0,         // Default
        true,      // Unsigned
        'CRC32-хэш транзакции (дата(timestamp).дебит(-1,1).сумма(float с 3 нолями после запятой).коммент(trim))',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Ключ для "CRC32-хэш транзакции"
    $objCMigration->setkey(
        "transacts_brief",     // table name
        "crc32",   //
        array("crc32"),    // fields
        true,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );


///////////////////////////////////////////////////////////////////////////////

    $objCMigration->dropTable(
        "transacts_detail",    // Table Name
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Таблица краткой информации о транзекции
    // цифры суффикса - последние 2 цифры id пользователя 
    $objCMigration->addTable(
        "transacts_detail",    // Table Name
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );


    // ID транзакции
    $objCMigration->setColumn(
        "transacts_detail",  // Table Name
        "transaction_id",   // Column name
        "INT",      // Column type
        11,         // Size
        false,         // Default
        true,
        'ID транзакции',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    // Ключ ID транзакции
    $objCMigration->setkey(
        "transacts_detail",     // table name
        "transaction_id",   //
        array("transaction_id"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );

    // Комментарий
    $objCMigration->setColumn(
        "transacts_detail",  // Table Name
        "comment",   // Column name
        "CHAR",      // Column type
        128,         // Size
        false,         // Default
        false,
        'Комментарий к транзакции',// Comment
        false,
        true,       // Multiply 
        2,          // Digits of prefix 
        10,         // Base of digit        
        "d"         // Format (decimal)
    );

    /*
    // Комментарий
    $objCMigration->setkey(
        "transacts_detail",     // table name
        "comment",   //
        array("comment"),    // fields
        false,           // unique
        true,           // multiply table
        2,              // myltiply suffix digits
        10,             // suffix digits base
        'd'             // suffix digits format
    );
    */



