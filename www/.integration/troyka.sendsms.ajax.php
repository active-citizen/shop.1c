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

    // Генерируем код подтверждения
    if(!$answer['error'])
        $objSMS->codeGenerate(); 
    if($objSMS->error)
        $answer['error'] = $objSMS->error;

    // Сохраняем код подтверждения
    if(!$answer['error'])
        $objSMS->codeSave($_REQUEST["cardnumber"]); 
    if($objSMS->error)
        $answer['error'] = $objSMS->error;

    // Отсылаем SMS
    if(!$answer['error'])
        $objSMS->codeSend($_REQUEST["cardnumber"]); 
    if($objSMS->error)
        $answer['error'] = $objSMS->error;
        

    echo json_encode($answer);

