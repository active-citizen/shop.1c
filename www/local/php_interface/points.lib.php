<?

    function pointsPeriodicUpdate(){
        // Если не надо обновлять баллы - идём мимо
        if(!pointsAlreadyUpdate())return false;
    }

    function pointsUpdate(){

        global $USER;

        require_once(
            $_SERVER["DOCUMENT_ROOT"]
                ."/.integration/classes/active-citizen-bridge.class.php"
        );
        require_once($_SERVER["DOCUMENT_ROOT"]
            ."/.integration/classes/user.class.php");
        require_once($_SERVER["DOCUMENT_ROOT"]
            ."/.integration/classes/point.class.php");
        $agBrige = new ActiveCitizenBridge;
        $bxUser = new bxUser;
        // Загружаем историю начисления баллов
        $session_id = $bxUser->getEMPSessionId();
       
        // Обновляем историю баллов
        $args = array(
            "session_id"    =>  $session_id,
            "token"         =>  $EMP_TOKENS[CONTOUR]
        );
        
        // Заливаем историю баллов и проверяем баланс пользователя 
        $agBrige->setMethod('pointsHistory');
        $agBrige->setMode('emp');
        $agBrige->setArguments($args);
        $history = $agBrige->exec();
        $bxPoint = new bxPoint;
        $bxPoint->updatePoints($history["result"], CUser::GetID());

        // Прописываем время последнего обновления баллов
        $USER->Update(
            $USER->GetId(), 
            array("UF_USER_LAST_UPDATE" => date("d.m.Y H:i:s"))
        );


    }

    /**
        Проверка необходимости подгрузить баллы по времени
    */
    function pointsAlreadyUpdate($nPointsUpdatePeriod = 600000){

        global $USER;
        $sUserLogin = $USER->GetLogin();

        // Обновляем только баллы авторизованных через АГ пользователей
        if(!preg_match("#^u\d+$#", $sUserLogin))return false;
    

        // Узнаём дату последнего обновления баллов у пользователя
        $arUserInfo = CUser::GetList(
            ($by="personal_country"), ($order="desc"),
            array("ID"=>CUser::GetId()),
            array(
                "SELECT"=>array("UF_USER_LAST_UPDATE","ID"),
                "NAV_PARAMS"=>array("nTopCount"=>1)
            )
        )->GetNext();
        
        $tmp = date_parse($arUserInfo["UF_USER_LAST_UPDATE"]);
        $sLastUpdateTimestamp = mktime(
            $tmp["hour"],$tmp["minute"],$tmp["second"],
            $tmp["month"],$tmp["day"],$tmp["year"]
        );
        $sNextUpdateTimestamp = $sLastUpdateTimestamp+$nPointsUpdatePeriod;

        if(time()>$sNextUpdateTimestamp)pointsUpdate();
    }
