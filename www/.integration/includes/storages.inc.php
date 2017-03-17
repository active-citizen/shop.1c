<?php
    ///////////////////////////////////////////////////////////////////////
    ///                     Импортируем склады
    ///////////////////////////////////////////////////////////////////////
    $arStoragesIndex = array();
    foreach($arStorages as $arStorage){

        $arStoragesIndex[$arStorage["Ид"]] = 0;

	$GPS_N = '';
	$GPS_S = '';
	list($GPS_N,$GPS_S) = explode(",",$arStorage["КоординатыНаКарте"]);
	$GPS_N = trim($GPS_N);
	$GPS_S = trim($GPS_S);

        $arFields = array();
        $arFields["TITLE"] = 
            $arStorage["НаименованиеПолное"]
            ?
            $arStorage["НаименованиеПолное"]
            :
            $arStorage["Наименование"];
        $arFields["ACTIVE"] = 'Y';
        $arFields["ADDRESS"] = $arStorage["ФактическийАдрес"];
        $arFields["DESCRIPTION"] = $arStorage["КакПроехать"];
        $arFields["GPS_N"] = $GPS_N;
        $arFields["GPS_S"] = $GPS_S;
        $arFields["IMAGE_ID"] = '';
        $arFields["PHONE"] = $arStorage["НомерТелефона"];
        $arFields["SCHEDULE"] = $arStorage["ГрафикРаботы"];
        $arFields["XML_ID"] = $arStorage["Ид"];
        $arFields["USER_ID"] = '';
        $arFields["EMAIL"] = $arStorage["Сайт"];
        $arFields["ISSUING_CENTER"] = 'Y';
        $arFields["SHIPPING_CENTER"] = 'Y';
        $arFields["SITE_ID"] = 's1';

	/*
        if(
            isset($arStorage["ФактическийАдрес"]) 
            && $arStorage["ФактическийАдрес"]
            && !is_array($arStorage["ФактическийАдрес"])
        )$arFields["ADDRESS"] .= $arStorage["ФактическийАдрес"];
            
        if(
            isset($arStorage["КакПроехать"]) 
            && $arStorage["КакПроехать"]
            && !is_array($arStorage["КакПроехать"])
        )$arFields["DESCRIPTION"] .= $arStorage["КакПроехать"];

        if(
            isset($arStorage["ГрафикРаботы"]) 
            && $arStorage["ГрафикРаботы"]
            && !is_array($arStorage["ГрафикРаботы"])
        )$arFields["SCHEDULE"] .= $arStorage["ГрафикРаботы"];
	*/

        $res = CCatalogStore::GetList(array(),array(
            "XML_ID"=>$arStorage["Ид"]
        ),false,array("nTopCount"=>1));

        if(!$existsStorage = $res->GetNext()){
            $arStoragesIndex[$arStorage["Ид"]] = CCatalogStore::Add($arFields);
        }
        else{
    	    /*
    	    if($existsStorage["ID"]==52){
    		echo "<pre>";
    		print_r($arStorage);
    		print_r($arFields);
    		print_r($existsStorage);
    		die;
    	    }
    	    */
            $arStoragesIndex[$arStorage["Ид"]] = $existsStorage["ID"];
            CCatalogStore::Update($existsStorage["ID"], $arFields);
        }
    }
