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

    $sPngFile = $_SERVER["DOCUMENT_ROOT"]."/../"
        ."renders/png/"
        .$nOrderId.".png"; 


    if(isset($_REQUEST["generate"])){
        $sQRCodeFilename = $_SERVER["DOCUMENT_ROOT"]."/../"
            ."renders/png/qr_"
            .$nOrderId.".png"; 
 
        $sCertFilename =
            $_SERVER["DOCUMENT_ROOT"]."/profile/order/cert_template.png";
//        $sRegularFont = "ALS_Direct_Regular.ttf";
//        $sBoldFont = "ALS_Direct_Bold.ttf";
        $sRegularFont = 'Regular.ttf';
        $sBoldFont = 'Bold.ttf';
        $sMonoRegularFont = 'Mono.ttf';
        $sMonoRegularFont = 'MonoRegular.ttf';
        $sMonoBoldFont = 'MonoBold.ttf';

        // ПОдключаем библиотеку QR-кодов
        require_once(
            $_SERVER["DOCUMENT_ROOT"]."/local/libs/phpqrcode/qrlib.php"
        );
        // Определяем содержимое RQ-кода
        $sQRText = "http://".$_SERVER["HTTP_HOST"]
            ."/partners/orders/".$nOrderId."/";
        // Формируем QR-код
        QRcode::png($sQRText,$sQRCodeFilename,QR_ECLEVEL_L
            ,6 // Размер одного пикселя QR в пискелях картинки
            ,0  // ПоляQR-кода в QR-пикселяях
        );

        $im = imagecreatefrompng($sCertFilename);

        // Получаем всю информацию о заказе
        $arOrder = initOrderGetInfo($nOrderId);
        // Определяем цвет основного текста
        $objColor = imagecolorallocate ( $im , 0, 0, 0);
        // Задаём цвет брендбуковского зелёного
        $objGreenColor = imagecolorallocate ( $im , 0, 122, 108);

        // Номер заказа
        $nFontSize = 40;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            355 , 170, 
            $objGreenColor, 
            $sMonoBoldFont,
            $arOrder["ORDER"]["ADDITIONAL_INFO"]
        );

        // Дата закрытия заказа        
        $nFontSize = 28;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            360 , 245, 
            $objColor, 
            $sMonoBoldFont,
            "до ". $DB->FormatDate(
                $arOrder["ORDER_PROPERTIES"]["CLOSE_DATE"]["VALUE"],
                "YYYY-MM-DD","DD.MM.YYYY"
            )
        );

        // ФИО заказчика
        $nFontSize = 16;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            120, 420, 
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
            120, 500, 
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
            120, 602, 
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
        $nFontSize = 14;
        $nHeight = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 960, 
            $objColor, 
            $sRegularFont,
            html2text($arOrder["PROPERTIES"]["RECEIVE_RULES"]["~VALUE"]["TEXT"])
            ,90
        );

        // Правила отмены
        $nFontSize = 14;
        $arText = drawWrappedText (
            $im, 
            $nFontSize, 0 , 
            122, 960+$nHeight+35, 
            $objColor, 
            $sRegularFont,
            html2text($arOrder["PROPERTIES"]["CANCEL_RULES"]["~VALUE"]["TEXT"])
            ,90
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
            730, 810, 
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
                730,
                370, 
                0, 0, 
                imagesx($imMap),
                imagesy($imMap)
            );
            
        }

        // QRCode
        if(file_exists($sQRCodeFilename)){
            $imQr = imagecreatefrompng($sQRCodeFilename);
            
            imagecopy(
                $im, $imQr,
                120,
                100, 
                0, 0, 
                imagesx($imQr),
                imagesy($imQr)
            );
            
        }

        // Служба поддержки
        $nFontSize = 16;
        $arText = imagettftext (
            $im, 
            $nFontSize, 0 , 
            442, 1638, 
            $objGreenColor, 
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

function html2text($html){
    $html = str_replace("\n","",$html);
    $html = preg_replace("#<p.*?>#i","\n",$html);
    $html = preg_replace("#<br.*?>#i","\n",$html);
    $html = preg_replace("#<li.*?>#i","\n - ",$html);
    $html = preg_replace("#<ul.*?>#i","\n",$html);
    $html = preg_replace("#<ol.*?>#i","\n",$html);
    return $html;
}
