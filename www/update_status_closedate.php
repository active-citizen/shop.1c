<?
    /**
        Скрипт массовой смены статусов и даты выполнения из csv
    */
    die;
    require($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    
    $fd = fopen("statuses.csv","r");
    $counter = 0;

    $arStatuses = array(
        "Аннулирован"   =>"AI",
        "Отменен"       =>"AG",
        "В работе"      =>"N",
        "Выполнен"      =>"F",
        "Брак"          =>"AC",
        "Новый"         =>"AA"
    );

    while(!feof($fd)){
        $sLine = fgets($fd);
        list($sOrder,$sStatus,$sDate) = explode("\t",$sLine);
        list($sPrefix,$sOrderNum) = explode("-",$sOrder);
        if(!$sDate=trim($sDate))continue;
        if(!$sStatus = trim($sStatus))continue;
        if(!isset($arStatuses[$sStatus]))continue;
        $sStatusId = $arStatuses[$sStatus];

        $tmp = date_parse($sDate);
        if($tmp["error_count"])continue;
        $sDate = 
            sprintf("%04d",$tmp["year"])
            ."-".sprintf("%02d",$tmp["month"])
            ."-".sprintf("%02d",$tmp["day"])
            ." ".sprintf("%02d",$tmp["hour"])
            .":".sprintf("%02d",$tmp["minute"])
            .":".sprintf("%02d",$tmp["second"])

        ;
        
        if($sPrefix!='НФСА')continue;
        if(!$nOrderNum=intval($sOrderNum))continue;
        if(!$sStatus=trim($sStatus))continue;
        $res = $DB->Query($sQuery = "
            SELECT ID,ADDITIONAL_INFO,STATUS_ID,COMMENTS,DATE_STATUS 
            FROM b_sale_order 
            WHERE 
                ADDITIONAL_INFO='".$sOrderNum."' 
                AND (
                    DATE_STATUS!='".$sDate."' 
                    OR STATUS_ID!='".$sStatusId."'
                )
            LIMIT 1
        ");
        if(!$res->result->num_rows)continue;
        
        $arOrder = $res->Fetch();
        $sComment = 
             "=script=\n"
            .(
                $arOrder["DATE_STATUS"]!=$sDate
                ?
                "Дата выполнения: ".$arOrder["DATE_STATUS"]." --- ".$sDate."\n"
                :
                ""
            )
            .(
                $arOrder["STATUS_ID"]!=$sStatusId
                ?
                "Статус: ".$arOrder["STATUS_ID"]." --- ".$sStatusId."\n"
                :
                ""
            )
            ; 

        $sQuery = "
            UPDATE
                b_sale_order
            SET 
                STATUS_ID='".$sStatusId."',
                DATE_STATUS='".$sDate."',
                COMMENTS = '".$sComment."'
            WHERE
                ID = '".$arOrder["ID"]."'
            LIMIT 1

        ";
        $DB->Query($sQuery);

        echo "$sLine<br/>";

        $counter++;
//        if($counter>100)break;

    }
