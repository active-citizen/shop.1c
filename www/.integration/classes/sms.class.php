<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");

    class СConfirmSMS{
        var $url = 'https://emp.mos.ru/site/sms/send';
        var $session_id = '';
        var $error = '';
        var $phone = '';
        var $code = 0;
        var $lifetime = 60;   // Время жизни высланного кода
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
                    .':Неверный формат телефона пользователя';
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
                    .':Некорректный формат номера карты тройки';
                return false;
            }

            if($arLink = $DB->Query("
                SELECT
                    `login`,
                    `id`
                FROM
                    `int_troika_link`
                WHERE
                    `cardnum`='".$DB->forSql($sTroykaCardNum)."'
                    AND
                    `confirmed`='1'
                LIMIT
                    1
            ")->Fetch()){
                $this->error = 'Карта тройки '.htmlspecialchars($sTroykaCardNum).'
                    закреплена за другим пользователем ';
                return false;
            }
            elseif($arLink = $DB->Query("
                SELECT
                    `login`,
                    `id`,
                    `sms_date`
                FROM
                    `int_troika_link`
                WHERE
                    `cardnum`='".$DB->forSql($sTroykaCardNum)."'
                    AND
                    `confirmed`='0'
                    AND
                    `sms_date`+".$this->lifetime.">=".time()."
                LIMIT
                    1
            ")->Fetch()){
                $this->error = 'Карта тройки '.htmlspecialchars($sTroykaCardNum)
                    .' в процессе закрепления. Был выслан код подтверждения. Повторно выслать SMS-код можно
                    через '.(($arLink['sms_date']+$this->lifetime)-time()).'
                    сек.';
                return false;
            }
            elseif($arLink = $DB->Query("
                SELECT
                    `login`,
                    `id`
                FROM
                    `int_troika_link`
                WHERE
                    `cardnum`='".$DB->forSql($sTroykaCardNum)."'
                    AND
                    `confirmed`='0'
                    AND
                    `sms_date`+".$this->lifetime."<".time()."
                LIMIT
                    1
            ")->Fetch()){
                $DB->Query("DELETE FROM `int_troika_link` WHERE
                `id`=".intval($arLink["id"])." LIMIT 1");
            }


            $sSql = "
                INSERT INTO `int_troika_link`(
                    `id`,
                    `login`,
                    `cardnum`,
                    `sms_date`,
                    `sms_code`,
                    `confirmed`
                )
                VALUES(
                    NULL,
                    '".$this->phone."',
                    '".$sTroykaCardNum."',
                    '".time()."',
                    '".$this->code."',
                    '0'
                )
            ";
            if(!$DB->Query($sSql,$ignore_errors=true)){
                $this->error = "<pre>".__CLASS__.':'.__LINE__
                    .print_r($DB->db_Conn->error_list[0]["error"],1);
                
                return false;
            }
            
        }

        

    }
