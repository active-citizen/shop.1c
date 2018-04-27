<?php

    // Токены для использования СС АГ
    $EMP_TOKENS = array(
        // Тестовый. Применяется на 
        //dev.shop.ag.mos.ru и pre-prod01.shop.ag.mos.ru
        "test"=>"",
        // UAT. Применяется на pre-prod02.shop.ag.mos.ru
        "uat"=>"",
        // PROD. Применяется на продакшене
        "prod"=>""
    );

   // Ключи для декодирования сессий с СС АГ
    $AG_KEYS = array(
        "test"=>array(
            "key"=>"",
        ),
        "uat"=>array(
            "key"=>"",
        ),
        "prod"=>array(
            "key"=>"",
        ),
    );

    $MAIL = array(
            "smtp.host"     =>  "eps-relay01.hq.corp.mos.ru",
            "smtp.port"     =>  "25",
            "smtp.user"     =>  "",
            "smtp.password" =>  "",
            "smtp.encrypt"  =>  "",
            "smtp.from"     =>  "shop@ag.mos.ru"
    );
      

