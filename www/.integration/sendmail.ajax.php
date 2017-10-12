<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 */

    /**
        Скрипт отсылки писем из очереди
    */

    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/common.php");

    // Получаем список писем в очереди
    $dd = opendir(LOCAL_MAIL_SMTP_QUEUE);
    $arMails = []; 
    while($sFilename = readdir($dd)){
        if($sFilename=='..' || $sFilename=='.' || $sFilename=='README.md')
            continue;
        $arMails[] = $sFilename;
    }

    // Отсылаем письма из очереди
    for($i=0,$c=count($arMails);$i<LOCAL_MAIL_SMTP_QUANT && $i<$c;$i++){
        send_from_eml(LOCAL_MAIL_SMTP_QUEUE."/".$arMails[$i]);
        echo $arMails[$i]."<br/>";    
    }
    

    

    require(
        $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
    );
    
    
    
