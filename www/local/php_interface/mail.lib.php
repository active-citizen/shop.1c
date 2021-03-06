<?
    /**
        Функция, вызываемая при сохранении статуса заказа
    */
    function eventOrderStatusSendEmail(
        $orderId, &$eventName, &$arFields, $orderStatus
    ){
    
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderId);
        // Добавляем информацию о письме в индекс
        require_once(
            $_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/CMailIndex.class.php"
        );
        $obMail = new CMailIndex;
        $sMailId = $obMail->add($orderId);


        // Зунуляем письма о заказах с опенкарта
        if(preg_match("#^\d+$#", $orderInfo["ORDER"]["ADDITIONAL_INFO"]))
            return true;

        // Текст письма
        $sMailText      =   '';
        // Текст вложения
        $sMailAttach    =   '';
        $sTo            =   $orderInfo["USER"]["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        // Название файла-вложения
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"].
            "-сертификат-АГ.png";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ ".
            $orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";

        // Метка для разделения частей письма
        $boundary = "--".md5(uniqid(rand().time()));
        // Общие заголовки письма
        $sHeaders = ""
            ."FROM: "."=?UTF-8?B?"
            .base64_encode(
                "«Активный гражданин». Магазин бонусов"
            )
            ."?="."<".$sFrom.">\r\n"
            ."Reply-To: "."=?UTF-8?B?"
            .base64_encode(
                "«Активный гражданин». Магазин бонусов"
            )."?="."<".$sFrom.">\r\n"
            ."Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n";
        ;

        // Заголовки вложения письма
        $sMailAttachHeaders = "\r\n--$boundary\r\n"
//            ."Content-Type: application/octet-stream; name=\"$sFilename\"\r\n"
            ."Content-Type: image/png; name=\"$sFilename\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-Disposition: attachment; filename=\"$sFilename\"\r\n"
            ."\r\n"
        ;

        // Заголовки основного текста письма 
        $sMailTextHeaders = "--$boundary\r\n"
            ."Content-Type: text/html; charset=UTF-8\r\n"
            //."Content-Transfer-Encoding: base64\r\n"
            ."\r\n"
        ;

        /*
        'AC'       // Брак
        'AG'       // Отмена
        'AI'       // Аннулировать
        'F'        // Выполнен
        'N'        // В работе
        */
        // Получаем в переменной $html шапку основного текта письма
        require(MAIL_TMPL_PATH."/_header.php");
        // Получаем в переменной $html шаблон письма, согласно коду статуса
        if(
            preg_match("#^\w+$#",$orderStatus)
            &&
            file_exists(
                MAIL_TMPL_PATH."/".$orderStatus."-".$orderInfo["TYPE"].".php"
            )
        )require(
            MAIL_TMPL_PATH."/".$orderStatus."-".$orderInfo["TYPE"].".php"
        );
        // Получаем в переменной $html подвал основного текста письма 
        require(MAIL_TMPL_PATH."/_footer.php");

        // Запоминаем текст основного письма
        $sMailText = $html;

        header("Content-type: text/plain; charset=utf-8;");
      
        // Определяем какую функцию для отправки почты использовать
        $sMailFunction = "mail";
        if(function_exists("custom_mail"))$sMailFunction = "custom_mail";
        
        // Отправляем письмо
        /*
        $sMailFunction(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                .$sMailText
                //.chunk_split(base64_encode($sMailText))
            //.$sMailAttachHeaders
            //    .chunk_split(base64_encode($sMailAttach))
            , 
            $sHeaders
        );
        */
        /**
            Если для заказа надо отправить сертификат
            И статус "в работе"
        */
        if(
            $orderInfo["SEND_CERT"]
            &&
            $orderStatus=='N'
        ){
            $_REQUEST["generate"] = 1;
            $_REQUEST["id"] = $orderId;
            require($_SERVER["DOCUMENT_ROOT"]."/profile/order/print.png.ajax.php");
            $sMailAttach = file_get_contents($sPngFile);
        }
//        echo chunk_split(base64_encode($sMailAttach));
//        die;

        $sFilename = $sMailFunction(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                //.chunk_split(base64_encode($sMailText))
                .$sMailText
                .(
                    $orderInfo["SEND_CERT"]
                    &&
                    $orderStatus=='N'
                    ?
                    $sMailAttachHeaders.chunk_split(
                        base64_encode($sMailAttach)
                    )
                    :
                    ""
                )
            , 
            $sHeaders
        );

        $obMail->setFilename($sMailId, $sFilename);

    }
    

    function eventSaleStatusEMail($orderId, $orderStatus){
    }

    /**
        Функция вызывается при создании заказа
        раньше высылал сертификат, теперь просто для красоты
    */
    function eventOrderNewSendEmail_normal($orderID, &$eventName, &$arFields){
        return false;
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderID);

        // Зунуляем письма о заказах с опенкарта
        if(
            preg_match("#^\d+$#", $orderInfo["ORDER"]["ADDITIONAL_INFO"])
        ) return true;
        
        $orderInfo["ORDER"]["ADDITIONAL_INFO"] = 
            "Б-".$orderInfo["ORDER"]["ID"];

        $sMailText      =   '';
        $sMailAttach    =   '';
        $sTo            =   $orderInfo["USER"]["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"]
            ."-сертификат-АГ.html";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ "
            .$orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";
        
        $boundary = "--".md5(uniqid(rand().time()));
        $sHeaders = ""
            ."FROM: "."=?UTF-8?B?"
            .base64_encode("«Активный гражданин». Магазин бонусов")
            ."?="."<".$sFrom
            .">\r\n"
            ."Reply-To: "."=?UTF-8?B?".
            base64_encode("«Активный гражданин». Магазин бонусов")
            ."?="."<".$sFrom.">\r\n"
            ."Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n";
        ;

        $sMailAttachHeaders = "\r\n--$boundary\r\n"
            ."Content-Type: text/html; UTF-8\r\n"
            ."Content-Type: application/octet-stream; name=\"$sFilename\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-Disposition: attachment; filename=\"$sFilename\"\r\n"
            ."\r\n"
        ;

        $sMailTextHeaders = "--$boundary\r\n"
            ."Content-Type: text/html; UTF-8\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."\r\n"
        ;

        require(MAIL_TMPL_PATH."/_header.php");
        require(MAIL_TMPL_PATH."/N-".$orderInfo["TYPE"].".php");
        require(MAIL_TMPL_PATH."/_footer.php");
        
        $sMailText = $html;

        /**
            Если для заказа надо отправить сертификат
        */
        if($orderInfo["SEND_CERT"]){
            require($_SERVER["DOCUMENT_ROOT"]."/profile/order/print.ajax.php");
            $sMailAttach = $sHTML;
        }
        
        $sMailFunction = "mail";
        if(function_exists("custom_mail"))$sMailFunction = "custom_mail";

        $sMailFunction(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                .chunk_split(base64_encode($sMailText))
                //.$sMailText
                .(
                    $orderInfo["SEND_CERT"]
                    ?
                    $sMailAttachHeaders.chunk_split(
                        base64_encode($sMailAttach)
                    )
                    :
                    ""
                )
            , 
            $sHeaders
        );
        
    }

    // Функции пустышки для предотвращения отправки писем при собятиях
    function eventOrderNewSendEmail_dummy($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderPaySendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOnOrderCancelSendEmail($orderID, &$eventName, &$arFields)
        {return false; }
    function eventOnOrderDeliverSendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderRemindSendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderRecurringCancelSendEmail(
        $orderID, &$eventName, &$arFields
    ){return false;}
    function eventOrderRecurringSendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderStatusSendEmailNull( $orderId, &$eventName, &$arFields, $orderStatus
    ){return false;}

    /**
     * Получаем информацию о заказе
    */
    function initOrderGetInfo($orderID){
        global $USER;
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

        $arOrder = CSaleOrder::GetList(
            array(), 
            array("ID"=>$orderID),
            false,
            array("nTopCount"=>1)
        )->GetNext();
        $arUser = CUser::GetByID($arOrder["USER_ID"])->GetNext();

        // Переформатирование даты
        $tmp = date_parse($arOrder["DATE_INSERT"]);
        $arOrder["DATE_INSERT"] 
            = sprintf("%02d",$tmp["day"])
            .".".sprintf("%02d",$tmp["month"])
            .".".$tmp["year"];

        /**
            Костыль для корректного выбора товара при заказе на сайте и при
            обмене из 1С. 
            
            Суть в том, что в момент заказа на сайте ID заказа 
            при вызове события отправки письма ещё не известен, и приходится
            опираться на FUSER

            А при обмене - обмен идёт от пользователя, который этого не
            заказывал, но известен номер заказа, поэтому опора на ORDER_ID
        */
        if(
            $_SERVER["SCRIPT_NAME"]=='/profile/order/print.png.ajax.php'
            ||
            $_SERVER["SCRIPT_NAME"]=='/profile/order/print.ajax.php'
        ){
            $arFilter = array(
                "ORDER_ID" => $arOrder["ID"],
                "LID"=>SITE_ID
            );
        }
        elseif(
            !$USER->isAdmin() 
        ){
            $arFilter = array(
                "FUSER_ID"=>CSaleBasket::GetBasketUserID(),
                "LID"=>SITE_ID,
                "ORDER_ID"=>"NULL"
            );
        }
        else{
            $arFilter = array(
                "ORDER_ID" => $arOrder["ID"],
                "LID"=>SITE_ID
            );
        }
 
        $arBasket = CSaleBasket::GetList(
            array(),
            $arFilter,
            false,
            array("nTopCount"=>1)
        )->GetNext();
        
        $arOffer = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arBasket["PRODUCT_ID"],
                "IBLOCK_ID" =>  OFFER_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array("ID","PROPERTY_CML2_LINK","NAME")
        )->GetNext();
        
        $arCatalog = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"],
                "IBLOCK_ID" =>  CATALOG_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array()
        )->GetNext();

        $arCatalogManufacturer = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"],
                "IBLOCK_ID" =>  CATALOG_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array("PROPERTY_MANUFACTURER_LINK")
        )->GetNext();
        
        $arProperties = array();
        $resProperties = CIBlockElement::GetProperty(
            CATALOG_IB_ID, $arCatalog["ID"]
        );
        while($ar = $resProperties->GetNext())$arProperties[$ar["CODE"]] = $ar;
        
        $arStatuses = array();
        $resStatus = CSaleStatus::GetList();
        while($ar = $resStatus->GetNext())$arStatuses[$ar["ID"]] = $ar;
        
        $arManufacturer = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arCatalogManufacturer[
                    "PROPERTY_MANUFACTURER_LINK_VALUE"
                ],
                "IBLOCK_ID" =>  MANUFACTURER_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array()
        )->GetNext();
        $arManufactProps = array();
        $resManufactProps = CIBlockElement::GetProperty(
            MANUFACTURER_IB_ID, $arManufacturer["ID"]
        );
        while( $ar = $resManufactProps->GetNext())
            $arManufactProps[$ar["CODE"]] = $ar;
        
        $arOrderProperties = orderGetProperties($orderID);
        $tmp = date_parse($arOrderProperties['CLOSE_DATE']['VALUE']);

        $arCatalog['EXPIRES'] = 
            sprintf("%02d", $tmp['day'])
            .".".sprintf("%02d", $tmp['month'])
            .".".sprintf("%04d", $tmp['year']);
       
        $send_cert = (
                (
                    isset($arProperties["SEND_CERT"]["VALUE"])
                    &&
                    $arProperties["SEND_CERT"]["VALUE"]
                )
                ?
                true
                :
                false
            );
        
        $type = "m";
        if($send_cert)$type = "v";
        
        return array(
            "USER"          =>  $arUser,
            "ORDER"         =>  $arOrder,
            "BASKET"        =>  $arBasket,
            "OFFER"         =>  $arOffer,
            "CATALOG"       =>  $arCatalog,
            "PROPERTIES"    =>  $arProperties,
            "STATUSES"      =>  $arStatuses,
            "MANUFACTURER"  =>  $arManufacturer,
            "MANUFACT_PROPS"=>  $arManufactProps,
            // M - материальное поощрение
            // V - виртуальное поощрение
            // R - поощрение-ресурс
            "TYPE"          =>  $type,
            "SEND_CERT"     =>  $send_cert,
            "ORDER_PROPERTIES"    =>  $arOrderProperties,
            "AR_FILTER"     => $arFilter,
            "AR_BASKET"     =>  $arBasket
        );
    }
 

