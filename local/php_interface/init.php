<?
    // Библиотаке для склонения баллов, и дней
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/rus.lib.php");
    // Библиотека для отправки почты через SMTP
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/common.php");
    
    CModule::IncludeModule("sale");
    CModule::IncludeModule("iblock");




    // Определяем ID инфоблока каталога
    $arr = CIBlock::GetList(array(),array("CODE"=>"clothes"))->GetNext();
    define("CATALOG_IB_ID",$arr["ID"]);
    // Определяем ID инфоблока предложений
    $arr = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"))->GetNext();
    define("OFFER_IB_ID",$arr["ID"]);
    
    define("SHOP_EMAIL","shop@ag.mos.ru");



    if(!preg_match("#^/admin#",$_SERVER["REQUEST_URI"])){
        define("ORDERS_EXCHANGE_ADMIN_MODE", true);
    }
    else{
        define("ORDERS_EXCHANGE_ADMIN_MODE", false);
    }

    // Если режим обмена заказами - глушим отправку письма при создании заказа
    if(0 && ORDERS_EXCHANGE_ADMIN_MODE){
        AddEventHandler("sale", "OnOrderNewSendEmail", "eventOrderNewSendEmail_dummy");
    }
    else{
        AddEventHandler("sale", "OnOrderNewSendEmail", "eventOrderNewSendEmail_normal");
    }
    
    // Глушим отправку письма при оплате
    AddEventHandler("sale", "OnOrderPaySendEmail", "eventOrderPaySendEmail");
    // Глушим отправку письма при отмене
    AddEventHandler("sale", "OnOrderCancelSendEmail", "eventOnOrderCancelSendEmail");
    // Глушим отправку письма при доставке
    AddEventHandler("sale", "OnOrderDeliverSendEmail", "eventOnOrderDeliverSendEmail");
    // Глушим отправку письма напоминалку
    AddEventHandler("sale", "OnOrderRemindSendEmail", "eventOrderRemindSendEmail");
    // Эти две фишни тоже на всякий случай баним
    AddEventHandler("sale", "OnOrderRecurringSendEmail", "eventOrderRecurringSendEmail");
    AddEventHandler("sale", "OnOrderRecurringCancelSendEmail", "eventOrderRecurringCancelSendEmail");
    
    
    
    // Назначаем обработчик формирования письма о смене статуса заказа
    AddEventHandler("sale", "OnSaleStatusEMail", "eventSaleStatusEMail");
    // Назначаем обработчик отправки письма о смене статуса заказа
    AddEventHandler("sale", "OnOrderStatusSendEmail", "eventOrderStatusSendEmail");
    
    
    function eventOrderStatusSendEmail($orderId, &$eventName, &$arFields, $orderStatus){
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderId);
        
        $sMailText      =   '';
        $sMailAttach    =   '';
        $sTo            =   $arFields["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"]."-сертификат-АГ.html";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ ".$orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";
        
        $boundary = "--".md5(uniqid(rand().time()));
        $sHeaders = ""
."FROM: "."=?UTF-8?B?".base64_encode("«Активный гражданин». Магазин бонусов")."?="."<".$sFrom.">\r\n"
."Reply-To: "."=?UTF-8?B?".base64_encode("«Активный гражданин». Магазин бонусов")."?="."<".$sFrom.">\r\n"
."Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"
."Content-Transfer-Encoding: base64\r\n";
;

        $sMailAttachHeaders = "\r\n--$boundary\r\n"
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

        // Определяем какой из почтовых шаблонов отсылать
        if($orderStatus=='AC'){     // Брак
        }
        elseif($orderStatus=='AG'){   // Отмена
        }
        elseif($orderStatus=='AI'){   // Аннулировать
        }
        elseif($orderStatus=='F'){    // Выполнен
        }
        elseif($orderStatus=='N'){    // В работе
        }
        custom_mail(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                .chunk_split(base64_encode($sMailText))
            .$sMailAttachHeaders
                .chunk_split(base64_encode($sMailAttach)), 
            $sHeaders
        );
    }
    

    function eventSaleStatusEMail($orderId, $orderStatus){
    }

    function eventOrderNewSendEmail_normal($orderID, &$eventName, &$arFields){
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderID);

        $sMailText      =   '';
        $sMailAttach    =   '';
        $sTo            =   $arFields["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"]."-сертификат-АГ.html";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ ".$orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";
        
        $boundary = "--".md5(uniqid(rand().time()));
        $sHeaders = ""
."FROM: "."=?UTF-8?B?".base64_encode("«Активный гражданин». Магазин бонусов")."?="."<".$sFrom.">\r\n"
."Reply-To: "."=?UTF-8?B?".base64_encode("«Активный гражданин». Магазин бонусов")."?="."<".$sFrom.">\r\n"
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
        $orderInfo["SEND_CERT"] = 1;
        if($orderInfo["SEND_CERT"]){
            require($_SERVER["DOCUMENT_ROOT"]."/profile/order/print.ajax.php");
            
            $sMailAttach = $sHTML;
        }
        
        custom_mail(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                .chunk_split(base64_encode($sMailText))
                .(
                    $orderInfo["SEND_CERT"]
                    ?
                    $sMailAttachHeaders.chunk_split(base64_encode($sMailAttach))
                    :
                    ""
                )
            , 
            $sHeaders
        );
        
    }

    // Функции пустышки для предотвращения отправки писем при собятиях
    function eventOrderNewSendEmail_dummy($orderID, &$eventName, &$arFields){return false;}
    function eventOrderPaySendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOnOrderCancelSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOnOrderDeliverSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOrderRemindSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOrderRecurringCancelSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOrderRecurringSendEmail($orderID, &$eventName, &$arFields){return false;}
    
    /**
     * Получаем информацию о заказе
    */
    function initOrderGetInfo($orderID){
        
        $arOrder = CSaleOrder::GetList(
            array(), 
            array("ID"=>$orderID),
            false,
            array("nTopCount"=>1)
        )->GetNext();
        
        $arBasket = CSaleBasket::GetList(
            array(),
            $arFilter = array(
                "FUSER_ID"=>CSaleBasket::GetBasketUserID(),
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL"
            ),
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
            array("PROPERTY_CML2_LINK")
        )->GetNext();
        
        $arCatalog = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arBasket["PROPERTY_CML2_LINK_VALUE"],
                "IBLOCK_ID" =>  CATALOG_IB_ID
            ),
            false,
            array()
        )->GetNext();
        
        $arProperties = array();
        $resProperties = CIBlockElement::GetProperty(CATALOG_IB_ID, $arCatalog["ID"]);
        while($ar = $resProperties->GetNext())$arProperties[$ar["CODE"]] = $ar;
        
        $arStatuses = array();
        $resStatus = CSaleStatus::GetList();
        while($ar = $resStatus->GetNext())$arStatuses[$ar["ID"]] = $ar;
        
        
        return array(
            "ORDER"         =>  $arOrder,
            "OFFER"         =>  $arOffer,
            "CATALOG"       =>  $arCatalog,
            "PROPERTIES"    =>  $arProperties,
            "STATUSES"      =>  $arStatuses,
            "SEND_CERT"     =>  (
                (
                    isset($orderInfo["PROPERTIES"]["SEND_CERT"]["VALUE"])
                    &&
                    $orderInfo["PROPERTIES"]["SEND_CERT"]["VALUE"]
                )
                ?
                true
                :
                false
            )

        );
    }

?>
