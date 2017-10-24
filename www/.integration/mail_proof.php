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

    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    $sMailId = $DB->ForSql(isset($_GET["id"])?$_GET["id"]:"");

    // Добавляем информацию о письме в индекс
    require_once(
        $_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/CMailIndex.class.php"
    );
    $obMail = new CMailIndex;
    if(
        $sMailId
        &&
        !preg_match("#shop.ag.mos.ru#",$_SERVER["HTTP_REFERER"])
    ) $sMailId = $obMail->setReceiveDate($sMailId);


    header("Content-type: image/gif");
    echo file_get_contents("empty.gif");

    require(
        $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
    );
    
    
    
