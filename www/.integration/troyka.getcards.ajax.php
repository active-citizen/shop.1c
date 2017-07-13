<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/sms.class.php");

    $answer = array(
        "error" => ""
    );

    // Создаём класс подтверждения по СМС, а при его инициализации получаем id
    // сессии, телефон
    $objSMS = new СConfirmSMS();
    if($objSMS->error)
        $answer['error'] = $objSMS->error;

    // Получаем список кард пользователя
    if(!$answer['error'])
        $answer['cards'] = $objSMS->getCards(); 
    if($objSMS->error)
        $answer['error'] = $objSMS->error;
        

    echo json_encode($answer);

