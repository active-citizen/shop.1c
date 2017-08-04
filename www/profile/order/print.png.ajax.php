<?
    define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
    define('BX_SECURITY_SESSION_READONLY', true);
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/rus.lib.php"
    );
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
        $sRegularFont = dirname(__FILE__).'/Regular.ttf';
        $sBoldFont = dirname(__FILE__).'/Bold.ttf';
        $sMonoRegularFont = dirname(__FILE__).'/MonoRegular.ttf';
        $sMonoBoldFont = dirname(__FILE__).'/MonoBold.ttf';

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

        global $DB;
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
            html_entity_decode(
                $arOrder["ORDER_PROPERTIES"]["PRODUCT_NAME"]["VALUE"]
            )
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


