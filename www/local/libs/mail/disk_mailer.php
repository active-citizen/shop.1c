<?php
    /**
        Функция сохранения отправляемых писем в файлах
    */
    function disk_custom_mail(
        $sTo,
        $sSubject,
        $sData,
        $sHeaders,
        $sMonth,
        $sDay,
        $sYear,
        $sHour,
        $sMin,
        $sSec,
        $sStoreFolder = ''
    ){
        $sBaseDir = $sStoreFolder
            ?
            $sStoreFolder
            :
            LOCAL_MAIL_DISK_BASEDIR;

        $arPath = array(
            "$sYear-$sMonth",
            "$sYear-$sMonth-$sDay",
            "$sTo"
        );

        $sSec .= sprintf("%04d", rand(0,10000));

        $sFilename = "$sYear-$sMonth-$sDay-$sHour-$sMin-$sSec-$sTo.eml";
        $sRelPath = '';
        if(!$sStoreFolder)foreach($arPath as $sFolder){
            $sRelPath .="/".$sFolder;
            $sFullPath = $sBaseDir.$sRelPath;
            if(!is_dir($sFullPath)){
                if(!mkdir($sFullPath))return false;
            }
        }
        if($sStoreFolder)$sFullPath = $sStoreFolder;
        if(!$fd = fopen($sFullPath."/".$sFilename,"w"))return false;
        fwrite($fd, "To: $sTo\r\n");
        fwrite($fd, "Subject: $sSubject\r\n");
        fwrite($fd, $sHeaders);
        fwrite($fd,"\r\n\r\n");
        fwrite($fd, $sData);
        fclose($fd);

        return true;
    }
