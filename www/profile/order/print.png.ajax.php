<?
    define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
    define('BX_SECURITY_SESSION_READONLY', true);
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php");
    global $USER;


    if(isset($orderInfo["ORDER"]["ID"]) && $orderInfo["ORDER"]["ID"]){
        $orderId = $orderInfo["ORDER"]["ID"];
    }
    elseif(isset($_GET['id']) && intval($_GET['id'])){
        $orderId = intval($_GET['id']);
    }

    if(!isset($orderInfo["ORDER"]))$orderInfo = initOrderGetInfo($orderId);

    CModule::IncludeModule('sale');
    CModule::IncludeModule('iblock');

    $arOrder = CSaleOrder::GetByID($orderId);


    // Проверяем чтобы заказ принадлежал пользователю
    if(
        !$USER->isAdmin() 
        && $arOrder["USER_ID"]!=$USER->GetId()
        && !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
        && !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())
    ){
        echo "Access denied";
        die;
    }


    $nOrderId = intval($_REQUEST["id"]);

    $sPanthomJsPath = dirname(__FILE__)."/phantomjs";
    $sTemplateJSFile = realpath(dirname(__FILE__))."/print.js";
    $sJSFile = $_SERVER["DOCUMENT_ROOT"]."/../"
        ."renders/js/"
        .$_COOKIE["PHPSESSID"]
        ."_".$nOrderId.".js"; 
    $sPngFile = $_SERVER["DOCUMENT_ROOT"]."/../"
        ."renders/png/"
        .$nOrderId.".png"; 

    if(isset($_REQUEST["generate"])){
        $sJs = file_get_contents($sTemplateJSFile);
        $sJs = str_replace("{PHPSESSID}",$_COOKIE["PHPSESSID"],$sJs);
        $sJs = str_replace("{ORDER_ID}",$nOrderId,$sJs);
        $sJs = str_replace("{CERT_PATH}",$sPngFile,$sJs);
        $sJs = str_replace("{PHPSESSID}",$_COOKIE["PHPSESSID"],$sJs);
//        echo "<pre>";
//        echo $sJs;
//        die;
        /*
        file_put_contents($sJSFile,$sJs);
        $sJSFile = realpath($sJSFile);
        $output=array();
        $sys = exec("$sPanthomJsPath '$sJSFile'", $output);
        unlink($sJSFile);
        */

        $sCertFilename =
            $_SERVER["DOCUMENT_ROOT"]."/profile/order/cert_template.png";
        $sRegularFont = "ALS_Direct_Regular.ttf";
        $sBoldFont = "ALS_Direct_Bold.ttf";


        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/phpqrcode/qrlib.php");

//        QRcode::png("My First QR Code");
        
//        die;

        $im = imagecreatefrompng($sCertFilename);

        /*
        $background = imagecolorallocate($im, 255, 255, 255);
        imagecolortransparent($im, $background);
        imagealphablending($im, false);
        imagesavealpha($im, true);            
        */

        $arOrder = initOrderGetInfo($nOrderId);
        $objColor = imagecolorallocate ( $im , 0, 0, 0);
        $objGreenColor = imagecolorallocate ( $im , 0, 122, 108);

        // Номер заказа
        $nFontSize = 40;
        /*
        $arBox = imagettfbbox($nFontSize, 0, $sRegularFont,
            $arOrder["ORDER"]["ADDITIONAL_INFO"]
        );
        */
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            355 , 170, 
            $objGreenColor, 
            $sRegularFont,
            $arOrder["ORDER"]["ADDITIONAL_INFO"]
        );

        // Дата закрытия заказа        
        $nFontSize = 32;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            360 , 245, 
            $objColor, 
            $sRegularFont,
            $DB->FormatDate(
                $arOrder["ORDER_PROPERTIES"]["CLOSE_DATE"]["VALUE"],
                "YYYY-MM-DD","DD.MM.YYYY"
            )
        );

        // ФИО заказчика
        $nFontSize = 16;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            122, 420, 
            $objColor, 
            $sBoldFont,
            mb_wordwrap(strip_tags(
            $arOrder["USER"]["LAST_NAME"]
                ." "
                .$arOrder["USER"]["NAME"]
                )
                ,45
                ,"\n"
            )
        );
        
        // Название поощрения
        $nFontSize = 16;
        $arText = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 500, 
            $objColor, 
            $sBoldFont,
            $arOrder["ORDER_PROPERTIES"]["PRODUCT_NAME"]["VALUE"]
            ,45
        );

        // Количество
        $nFontSize = 16;
        $arText = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 602, 
            $objColor, 
            $sBoldFont,
            $arOrder["PROPERTIES"]["QUANT"]["VALUE"]
                .(
                    $arOrder["BASKET"]["QUANTITY"]>1
                    ?
                    " X ".$arOrder["BASKET"]["QUANTITY"]
                    :
                    ""
                )
           ,45
        );
        
        // Адрес
        $nFontSize = 16;
        $arText = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 680, 
            $objColor, 
            $sBoldFont,
            $arOrder["MANUFACT_PROPS"]["ADDRESS"]["VALUE"]
            ,45
        );

        // Правила получения
        $nFontSize = 16;
        $nHeight = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 960, 
            $objColor, 
            $sRegularFont,
                $arOrder["PROPERTIES"]["RECEIVE_RULES"]["VALUE"]["TEXT"]
                ,80
        );

        // Правила отмены
        $nFontSize = 16;
        $arText = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 960+$nHeight+35, 
            $objColor, 
            $sRegularFont,
            $arOrder["PROPERTIES"]["CANCEL_RULES"]["VALUE"]["TEXT"]
            ,80
        );
        $sMapFilename = $_SERVER["DOCUMENT_ROOT"]."/upload/manufacturers/"
            .$arOrder["PROPERTIES"]["MANUFACTURER_LINK"]["VALUE"]
            .".png"
        ;

        // Как проехать
        $nFontSize = 16;
        $arText = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            680, 810, 
            $objColor, 
            $sRegularFont,
            $arOrder["MANUFACT_PROPS"]["HOW_FIND"]["VALUE"],
            32
        );

        $sMapFilename = $_SERVER["DOCUMENT_ROOT"]."/upload/manufacturers/"
            .$arOrder["PROPERTIES"]["MANUFACTURER_LINK"]["VALUE"]
            .".png"
        ;

        // Карта
        if(file_exists($sMapFilename)){
            $imMap = imagecreatefrompng($sMapFilename);
            
            imagecopy(
                $im, $imMap,
                680,
                370, 
                0, 0, 
                imagesx($imMap),
                imagesy($imMap)
            );
            
        }


        // Служба поддержки
        $nFontSize = 16;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            442, 1638, 
            $objColor, 
            $sRegularFont,
            mb_wordwrap(strip_tags(
                "support@ag.mos.ru"
                )
                ,80
                ,"\n"
            )
        );



//        echo "<pre>";
//        print_r($arOrder["MANUFACT_PROPS"]["HOW_FIND"]["VALUE"]);
//        die;


        imagepng($im, $sPngFile);
        imagedestroy($im);
    }

    $stat = stat($sPngFile);


    if($_REQUEST["act"]=='download'){
        header("Content-type: image/png");
        header("Content-length: ".$stat["size"]);
        header("Content-disposition: attachment; filename=\"".$nOrderId.".png\"");

        echo file_get_contents($sPngFile);
    }
    elseif($_REQUEST["act"]=='get'){
        header("Content-type: image/png");
        header("Content-length: ".$stat["size"]);

        echo file_get_contents($sPngFile);
    }
    elseif($_REQUEST["act"]=='print'){
        ?>
        <html>
            <head>
            </head>
            <body>
            <img src="data:image/png;base64,<?=  
                base64_encode(file_get_contents($sPngFile))
            ?>" style="width:100%">
            <script>
                window.print();                
            </script>
            </body>
        </html>
        <?
    }



function mb_wordwrap($str, $width = 75, $break = "\n", $cut = false) {
    $lines = explode($break, $str);
    foreach ($lines as &$line) {
        $line = rtrim($line);
        if (mb_strlen($line) <= $width)
            continue;
        $words = explode(' ', $line);
        $line = '';
        $actual = '';
        foreach ($words as $word) {
            if (mb_strlen($actual.$word) <= $width)
                $actual .= $word.' ';
            else {
                if ($actual != '')
                    $line .= rtrim($actual).$break;
                $actual = $word;
                if ($cut) {
                    while (mb_strlen($actual) > $width) {
                        $line .= mb_substr($actual, 0, $width).$break;
                        $actual = mb_substr($actual, $width);
                    }
                }
                $actual .= ' ';
            }
        }
        $line .= trim($actual);
    }
    return implode($break, $lines);
}

function drawWrappedText (
    &$im, 
    $nFontSize, 
    $nAngle,
    $nX, $nY,
    $objColor, 
    $sFont,
    $sText,
    $nWidth,
    $nLineHeight = 0.5
){
    $nMaxX = 0;
    $nMaxY = 0;
    
    $arText = explode("\n",mb_wordwrap($sText,$nWidth,"\n"));
    foreach($arText as $sLine){
        $arCoords = imagettftext (
            $im, 
            $nFontSize, $nAngle, 
            $nX, $nY+$nMax, 
            $objColor, 
            $sFont,
            html_entity_decode($sLine)
        );
        $nMax += ($arCoords[1]-$arCoords[5]+$nLineHeight*$nFontSize);
    }

    return $nMax;
}
