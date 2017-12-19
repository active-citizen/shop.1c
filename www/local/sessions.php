<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    if(!$USER->IsAdmin())die("Access denied");
    header("Content-type: text/plain; charset=utf-8");
    header("Content-Disposition:attachment; filename=\"sessions.txt\"");
    
    
    $sQuery = "SELECT * FROM `int_profile_import`";
    $res = $DB->Query($sQuery);
    while($arSession = $res->Fetch()){
        foreach($arSession as $sKey=>$sValue)echo "$sValue\t";
        echo "\r\n";
    }
