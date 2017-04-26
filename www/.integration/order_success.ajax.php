<?php

    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    if(!$USER->IsAdmin()){
        echo "failure\nAccess Denied\n";
        die;
    }

    header("Content-type: text/plain; charset=windows-1251;");
    $session_id = 
        isset($_COOKIE['PHPSESSID'])
        ?
        $_COOKIE['PHPSESSID']
        :
        "";
    $session_id = 
        !$session_id && isset($_POST['PHPSESSID'])
        ?
        $_POST['PHPSESSID']
        :
        $session_id;
    $session_id = 
        !$session_id && isset($_GET['PHPSESSID'])
        ?
        $_GET['PHPSESSID']
        :
        $session_id;

    if(!preg_match("/^[\d\w]+$/",$session_id)){
        echo "Failed\nPHPSESSID incorrect";
        die;
    }
    
    CModule::IncludeModule('sale');
  
    // Получаем заказы отданный е рамках этой сессии
    $res = CSaleOrder::GetList(
        array(),
        array(
            "DATE_UPDATE"=>"",
            "COMMENTS"=>$session_id
        ), // Выводить только не отданные заказы
        false
    );
   
    $objOrder = new CSaleOrder;
    $arOrders = array();
    while($arrOrder = $res->GetNext()){
        // Не выводим заказы импортированные из других систем
        //if(!$arrOrder["EMP_PAYED_ID"])continue;
        if(!preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"]))continue;

        // Отмечаем заказ как "отданный в рамках транзакции $session_id"
        
        $objOrder->Update(
            $arrOrder["ID"],
            array(
                "COMMENTS"=>""
            )
        );
        
    }
    echo "success";
        
        
 
