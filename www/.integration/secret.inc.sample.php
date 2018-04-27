<?php
// этот файл

// Токены для использования СС АГ
$EMP_TOKENS = [
    // Тестовый. Применяется на
    //dev.shop.ag.mos.ru и pre-prod01.shop.ag.mos.ru
    "test" => "",
    // UAT. Применяется на pre-prod02.shop.ag.mos.ru
    "uat" => "",
    // PROD. Применяется на продакшене
    "prod" => ""
];

// Ключи для декодирования сессий с СС АГ
$AG_KEYS = [
    "test" => [
        "key" => "",
    ],
    "uat" => [
        "key" => "",
    ],
    "prod" => [
        "key" => "",
    ],
];


$AG_SECRETS = [
    "test" => [
        "secret" => "",
        "local_url" => "",
        "local_port" => "80",
        "ext_url" => ""
    ],
    "uat" => [
        "secret" => "",
        "local_url" => "",
        "local_port" => "8091",
        "ext_url" => ""
    ],
    "prod" => [
        "secret" => "",
        "local_url" => "",
        "local_port" => "8090",
        "ext_url" => ""
    ],
];


$MAIL = [
    "smtp.host" => "",
    "smtp.port" => "25",
    "smtp.user" => "",
    "smtp.password" => "",
    "smtp.encrypt" => true,
    "smtp.from" => ""
];

$TROYKA_PEM_PATH = $_SERVER["DOCUMENT_ROOT"] . "/.integration/troyka.pem";

