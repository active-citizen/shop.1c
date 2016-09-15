<?php
    ///////////////////////////////////////////////////////////////////////
    ///                     Импортируем склады
    ///////////////////////////////////////////////////////////////////////
    $arStoragesIndex = array();
    foreach($arStorages as $arStorage){

        $arStoragesIndex[$arStorage["Ид"]] = 0;

        $arFields = array();
        $arFields["TITLE"] = $arStorage["Наименование"];
        $arFields["ACTIVE"] = 'Y';
        $arFields["ADDRESS"] = '';
        $arFields["DESCRIPTION"] = '';
        $arFields["GPS_N"] = '';
        $arFields["GPS_S"] = '';
        $arFields["IMAGE_ID"] = '';
        $arFields["PHONE"] = '';
        $arFields["SCHEDULE"] = '';
        $arFields["XML_ID"] = $arStorage["Ид"];
        $arFields["USER_ID"] = '';
        $arFields["EMAIL"] = '';
        $arFields["ISSUING_CENTER"] = '';
        $arFields["SHIPPING_CENTER"] = '';
        $arFields["SITE_ID"] = 's1';
        

        if(
            isset($arStorage["КакПроехать"]) 
            && $arStorage["КакПроехать"]
            && !is_array($arStorage["КакПроехать"])
        )$arFields["ADDRESS"] .= $arStorage["КакПроехать"];
            
        if(
            isset($arStorage["ДополнительнаяИнформация"]) 
            && $arStorage["ДополнительнаяИнформация"]
            && !is_array($arStorage["ДополнительнаяИнформация"])
        )$arFields["DESCRIPTION"] .= $arStorage["ДополнительнаяИнформация"];

        if(
            isset($arStorage["ГрафикРаботы"]) 
            && $arStorage["ГрафикРаботы"]
            && !is_array($arStorage["ГрафикРаботы"])
        )$arFields["SCHEDULE"] .= $arStorage["ГрафикРаботы"];

        $res = CCatalogStore::GetList(array(),array(
            "XML_ID"=>$arStorage["Ид"]
        ),false,array("nTopCount"=>1));

        if(!$existsStorage = $res->GetNext()){
            $arStoragesIndex[$arStorage["Ид"]] = CCatalogStore::Add($arFields);
        }
        else{
            $arStoragesIndex[$arStorage["Ид"]] = $existsStorage["ID"];
            CCatalogStore::Update($existsStorage["ID"], $arFields);
        }
    }
