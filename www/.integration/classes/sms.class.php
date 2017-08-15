<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/curl.class.php");


    class СConfirmSMS{
        var $url = 'https://emp.mos.ru/site/sms/send';
        var $session_id = '';
        var $error = '';
        var $phone = '';
        var $code = 0;
        var $lifetime = 5;  // Минимальное время тарифа "хорош пиздеть" 
        // После каждой попытки к времени ожидания следующей попытки
        // прибавляется степень двойки от числа попыток. То есть 5 попыток
        // сделают время ожидания следующей минимум 10 минут
        // а после 16 попыток придётся отсиживаться почти сутки
        var $expire = 86400;// Время жизни высланного кода
        var $max_code_tries = 5;    // Максимальное число попыток ввода кода
        function __construct(){

            // Получаем логин пользователя (а значит и телефон)
            $arUser = CUser::GetById(CUser::GetId())->Fetch();
            if(!isset($arUser["LOGIN"])){
                $this->error = __CLASS__.':'.__LINE__
                    .':Пользователь не авторизован';
                return false;
            }
            $sLogin = $arUser["LOGIN"];
            $this->phone = str_replace("u","",$arUser["LOGIN"]);
            if(!preg_match("#^7\d{10}$#",$this->phone)){
                $this->error = __CLASS__.':'.__LINE__
                    .':Неверный формат телефона пользователя:'.
                    '"'.$this->phone.'"';
                return false;
            }

            // Получаем текущую сессию пользователя
            $objUser = new bxUser();          
            if(!$sSessionId = $objUser->getEMPSessionId($sLogin)){
                $this->error = __CLASS__.':'.__LINE__
                    .':Не удалось получить сессию пользователя';
                return false;
            }

            $this->session_id = $sSessionId;
        }

        function codeGenerate(){
            srand((time()+ip2long($_SERVER["REMOTE_ADDR"]))%1000000);
            $this->code = sprintf("%05d",rand(0,100000)-1);
        }

        function codeSave($sTroykaCardNum, $sCode = ''){
            global $DB;

            if(!$sCode)$sCode = $this->code;

            if(!$sCode){
                $this->error = __CLASS__.':'.__LINE__
                    .':подтверждения';
                return false;
            }

            if(!preg_match("#^\d{5}$#",$sCode)){
                $this->error = __CLASS__.':'.__LINE__
                    .':Некорректный формат кода подтверждения';
                return false;
            }

            if(!preg_match("#^\d{10}$#",$sTroykaCardNum)){
                $this->error = __CLASS__.':'.__LINE__
                    .':Некорректный формат номера карты тройки - '
                    .$sTroykaCardNum;
                return false;
            }

            $nSendTries = 0;
            if($arLink = $DB->Query("
                SELECT
                    `login`,
                    `id`,
                    `send_tries`,
                    `code_tries`
                FROM
                    `int_troika_link`
                WHERE
                    `cardnum`='".$DB->forSql($sTroykaCardNum)."'
                    AND
                    `confirmed`='1'
                LIMIT
                    1
            ")->Fetch()){
                if($arLink['login']!=$this->phone)
                    $this->error = 'Карта тройки '.htmlspecialchars($sTroykaCardNum).'
                        пополнялась с другого аккаунта. К сожалению, Вы не можете
                        пополнить эту карту с данного аккаунта. Обратитесь за
                        разъяснениями в службу технической поддержки ';
                else    
                    $this->error = 'Карта '.htmlspecialchars($sTroykaCardNum).'
                        уже закреплена за Вами';
                return false;
            }
            elseif($arLink = $DB->Query("
                SELECT
                    `login`,
                    `id`,
                    `sms_date`,
                    `send_tries`,
                    `code_tries`

                FROM
                    `int_troika_link`
                WHERE
                    `cardnum`='".$DB->forSql($sTroykaCardNum)."'
                    AND
                    `confirmed`='0'
                    AND
                    `sms_date`+".$this->lifetime."+POW(2,`send_tries`)>=".time()."
                LIMIT
                    1
            ")->Fetch()){
                $this->error =  'Был выслан код подтверждения. '
                .'Повторно выслать код можно через '
                .(
                    (
                        $arLink['sms_date']
                        +
                        $this->lifetime
                        +pow(2,$arLink["send_tries"])
                    )
                    -
                    time()
                 )
                .' сек.';
                return false;
            }
            elseif($arLink = $DB->Query("
                SELECT
                    `login`,
                    `id`,
                    `send_tries`,
                    `code_tries`

                FROM
                    `int_troika_link`
                WHERE
                    `cardnum`='".$DB->forSql($sTroykaCardNum)."'
                    AND
                    `confirmed`='0'
                    AND
                    `sms_date`+".$this->lifetime."+POW(2,`send_tries`)<".time()."
                LIMIT
                    1
            ")->Fetch()){
                $nSendTries = $arLink['send_tries']+1;
                $DB->Query("
                    UPDATE 
                        `int_troika_link` 
                    SET
                        `send_tries` = '$nSendTries',
                        `login` = '".$this->phone."',
                        `sms_date` = '".time()."',
                        `sms_code` = '".$this->code."'
                    WHERE
                        `id`=".intval($arLink["id"])." 
                    LIMIT 1
                ");
            }
            else{
                $sSql = "
                    INSERT INTO `int_troika_link`(
                        `id`,
                        `login`,
                        `cardnum`,
                        `sms_date`,
                        `sms_code`,
                        `confirmed`,
                        `send_tries`
                    )
                    VALUES(
                        NULL,
                        '".$this->phone."',
                        '".$sTroykaCardNum."',
                        '".time()."',
                        '".$this->code."',
                        '0',
                        '$nSendTries'
                    )
                ";
                if(!$DB->Query($sSql,$ignore_errors=true)){
                    $this->error = "<pre>".__CLASS__.':'.__LINE__
                        .print_r($DB->db_Conn->error_list[0]["error"],1);
                    
                    return false;
                }
            }
            
        }

        function codeSend($sTroykaCardNum, $sCode = ''){

            if(!$sCode)$sCode = $this->code;

            if(!$sCode){
                $this->error = __CLASS__.':'.__LINE__
                    .':подтверждения';
                return false;
            }

            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            $arRequest = array(
                "token" => $EMP_TOKENS['prod'],
                "auth"  =>array(
                    "session_id"    =>  $this->session_id,
                ),
                "text"=>$this->code. ' - ваш код для подтверждения карты Тройка '
                    .' на сайте '.$_SERVER["HTTP_HOST"]
            );
            $sPostData = json_encode($arRequest);
            
            $objCurl = new curlTool();
            $sAnswer = $objCurl->post(
                $this->url,
                $sPostData
            );
            if(!$objAnswer = json_decode($sAnswer)){
                $this->error = __CLASS__.':'.__LINE__
                    .'Не могу распарсить ответ SMS-шлюза'
                    ;
                return false;
            }
            if(
                property_exists($objAnswer, "errorMessage") 
                && !$objAnswer->errorMessage
            ){
                return true;
            }

            if(
                property_exists($objAnswer, "errorMessage") 
                && $objAnswer->errorMessage
            ){
                $this->error = __CLASS__.':'.__LINE__
                    .$objAnswer->errorMessage
                    ;
                return false;
            }

            $this->error = __CLASS__.':'.__LINE__
                .'Ошибка SMS-шлюза'
                ;
            return false;

        }

        /**
            Проверяет корректность сода подтверждения присланному в SMS
        */
        function codeCheck($sCardNumber,$sCode){

            global $DB;

            if(!preg_match("#^\d{10}$#",$sCardNumber)){
                $this->error = __CLASS__.':'.__LINE__
                    .'Некорректный формат номера карты Тройки'
                    ;
                return false;
            }

            if(!preg_match("#^\d{5}$#",$sCode)){
                $this->error = ''
//                    .__CLASS__.':'.__LINE__
                    .'Некорректный формат кода подтверждения'
                    ;
                return false;
            }

            if(!$arLink = $DB->Query("
                SELECT
                    *
                FROM
                    `int_troika_link`
                WHERE   
                    `cardnum` = '$sCardNumber'
                LIMIT   
                    1
            ")->Fetch()){
                $this->error = __CLASS__.':'.__LINE__
                    .'По карте '.$sCardNumber.' не высылался код подтвержденя'
                    ;
                return false;
            }

            if($arLink["sms_date"]+$this->expire<time()){
                $this->error = 
                    ''
//                    .__CLASS__.':'.__LINE__
                    .'Время жизни кода подтверждения для карты '.$sCardNumber.' вышло'
                    ;
                return false;
            }

            if($arLink["code_tries"]>=$this->max_code_tries){
                $this->error = 
                    ''
                    .'Число попыток ввода кода подтверждения исчерпано. '
                    .'Повторите попытку заказа'
                    ;
                return false;
            }

            if($sCode==$arLink["sms_code"]){
                $DB->Query("
                    UPDATE 
                        `int_troika_link` 
                    SET 
                        `confirmed`='1' 
                    WHERE
                        `id`='".$arLink['id']."'
                    LIMIT
                        1
                ");
                return true;
            }
            else{
                $DB->Query("
                    UPDATE 
                        `int_troika_link` 
                    SET 
                        `code_tries`='".($arLink["code_tries"]+1)."'
                    WHERE
                        `id`='".$arLink['id']."'
                    LIMIT
                        1
                ");
                $this->error = 
                    ''
                    .'Введён неверный код. Осталось попыток:'
                    .($this->max_code_tries-$arLink["code_tries"])
                    ;
                return false;
            }

            return false;
        }

        /**
            Возвращает массив карт Тройка, закреплённых за данным номером
        */
        function getCards(){
            global $DB;

            $resCards = $DB->Query(
                "
                    SELECT
                        `cardnum`
                    FROM
                        `int_troika_link`
                    WHERE
                        `login` = '".$this->phone."'
                        AND
                        `confirmed` = '1'
                "
            ); 

            $arResult = array();
            while($arCard = $resCards->Fetch())$arResult[] = $arCard["cardnum"];
            return $arResult;
        }

        

    }
