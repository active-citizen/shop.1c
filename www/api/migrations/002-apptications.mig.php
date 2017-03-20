#!/usr/bin/php
<?php
    
    require(realpath(dirname(__FILE__).
        "/../include/classes/CMigration.class.php"));


    $objCMigration = new CMigration();

    // Таблица приложений 
    $objCMigration->dropTable( "applications");
    $objCMigration->addTable( "applications");
    
    // Название приложения 
    $objCMigration->setColumn(
        "applications",  // Table Name
        "name",       // Column name
        "CHAR",      // Column type
        255,         // Size
        false,         // Default
        false,
        'Название приложения'// Comment
    );

    // Символьный код приложения
    $objCMigration->setColumn(
        "applications",  // Table Name
        "code",       // Column name
        "CHAR",      // Column type
        16,         // Size
        false,         // Default
        false,
        'Символьный код приложения'// Comment
    );

    // Ключ для симвоольного кода
    $objCMigration->setkey(
        "applications",     // Table name
        "code",   //
        array("code"),    // Fields
        true           // Unique
    );
    
    $id = $GLOBALS["DB"]->insert("applications",array(
        "name"=>"Магазин поощрений Активный Гражданин",
        "code"=>"ag_shop",
        "ctime"=>date("Y-m-d H:i:s")
    ));
    $GLOBALS["DB"]->update("applications",array("id"=>1),array("id"=>$id));

    $id = $GLOBALS["DB"]->insert("applications",array(
        "name"=>'Портал "Активный Гражданин"',
        "code"=>"ag",
        "ctime"=>date("Y-m-d H:i:s")
    ));
    $GLOBALS["DB"]->update("applications",array("id"=>2),array("id"=>$id));


