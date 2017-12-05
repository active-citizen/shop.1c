<?php
    
    // Загружаем XML
    $xmlImport = file_get_contents($uploadDir.$importFilename);
    $obImport = simplexml_load_string($xmlImport, "SimpleXMLElement" );

    // Преобразуем объект к массиву
    $arImport = json_decode(json_encode((array)$obImport), TRUE);        
    
    // Получаем из XML массив групп товаров
    $arGroups = $arImport["Классификатор"]["Группы"]["Группа"];
    if(!isset($arGroups[0]))$arGroups = array($arGroups);
    // Получаем из XML массив производителей
    $arManufacturers = $arImport["Классификатор"]["Производители"]["Производитель"];
    if(!isset($arManufacturers[0]))$arManufacturers = array($arManufacturers);
    // Получаем из XML массив хотелок
    $arIwant = $arImport["Классификатор"]["Хотелки"]["Хочу"];
    if(!isset($arIwant[0]))$arIwant = array($arIwant);
    // Получаем из XML массив интересов
    $arInterests = $arImport["Классификатор"]["Интересы"]["Интересуюсь"];
    if(!isset($arInterests[0]))$arInterests = array($arInterests);
     
    // Получаем из XML массив товаров
    $arProducts = $arImport["Каталог"]["Товары"]["Товар"];
    if(!isset($arProducts[0]))$arProducts = array($arProducts);

    // Приводим картинки к массивному виду
    foreach($arProducts as $k=>$arProduct)
        if(isset($arProducts[$k]["Картинка"]) && !is_array($arProducts[$k]["Картинка"]))
            $arProducts[$k]["Картинка"] = array($arProducts[$k]["Картинка"]);

    /////////////////////////////////////////////////////////////////////////
    // Импортируем хотелки и интересы и составляем индекс
    ////////////////////////////////////////////////////////////////////////

    // Составляем индекс уже существующих в битриксе хотелок
    $resPropertiesEnum = CIBlockElement::GetList(
        array(), array( "IBLOCK_ID"=>IWANT_IBLOCK_ID)
    );
    $arAlreadyBxIWantIndex = array();
    while($arWant = $resPropertiesEnum->getNext())$arAlreadyBxIWantIndex[$arWant["XML_ID"]] = $arWant;

    // Составляем индекс пришедших в XML хотелок
    $arXMLWantsIndex = array();
    foreach($arIwant as $index)$arXMLWantsIndex[$index["Ид"]] = $index;

    // Находим среди битриксовых хотел те, которых нет в XML и удаляем их
    foreach($arAlreadyBxIWantIndex as $XML_ID=>$arWantItem)
        if(!isset($arXMLWantsIndex[$XML_ID])){
            CIBlockElement::Delete($arWantItem["ID"]);
        }


    // Находим среди XML хотелки, которых нет среди битриксовых и добавляем
    // И вносим в индекс
    $CIBlockElement = new CIBlockElement;
    foreach($arXMLWantsIndex as $XML_ID=>$arWantItem){
        $arFields = array(
            "IBLOCK_ID"     =>  IWANT_IBLOCK_ID,
            "NAME"          =>  $arWantItem["Наименование"],
            "XML_ID"        =>  $arWantItem["Ид"],
            "ACTIVE"        =>  $arWantItem["Включен"]=='Да'?"Y":"N"
        );
        if(!isset($arAlreadyBxIWantIndex[$XML_ID])){
            $nIWID = $CIBlockElement->Add($arFields);
            $arAlreadyBxIWantIndex[$arWantItem["Ид"]] = array(
                "NAME"  =>  $arWantItem["Наименование"],
                "ID"    =>  $nIWID
            );
        }
        else{
            $CIBlockElement->Update($arAlreadyBxIWantIndex[$XML_ID]["ID"],$arFields);            
        }
    }
    $arIwantIndex = $arAlreadyBxIWantIndex;

    // Составляем индекс уже существующих в битриксе интересов
    $resPropertiesEnum = CIBlockElement::GetList(
        array(), array( "IBLOCK_ID"=>INTEREST_IBLOCK_ID)
    );
    $arAlreadyBxInterestIndex = array();
    while($arInterest = $resPropertiesEnum->getNext())
        $arAlreadyBxInterestIndex[$arInterest["XML_ID"]] = $arInterest;
    // Составляем индекс пришедших в XML интересов 
    $arXMLInterestIndex = array();
    foreach($arInterests as $index)$arXMLInterestIndex[$index["Ид"]] = $index;

    // Находим среди битриксовых интересов те, которых нет в XML и удаляем их
    foreach($arAlreadyBxInterestIndex as $XML_ID=>$arInterestItem)
        if(!isset($arXMLInterestIndex[$XML_ID]))
            CIBlockElement::Delete($arInterestItem["ID"]);


    // Находим среди XML интекресы, которых нет среди битриксовых и добавляем
    // И вносим в индекс
    // Те, которые есть - обновляем
    foreach($arXMLInterestIndex as $XML_ID=>$arInterest){
        $arFields = array(
            "IBLOCK_ID"     =>  INTEREST_IBLOCK_ID,
            "NAME"          =>  $arInterest["Наименование"],
            "XML_ID"        =>  $arInterest["Ид"],
            "ACTIVE"        =>  $arInterest["Включен"]=='Да'?"Y":"N"
        );
        if(!isset($arAlreadyBxInterestIndex[$XML_ID])){
            $nInterestID = $CIBlockElement->Add($arFields);
            $arAlreadyBxInterestIndex[$arInterest["Ид"]] = array(
                "NAME"  =>  $arInterest["Наименование"],
                "ID"    =>  $nInterestID
            );
        }
        else{
            $CIBlockElement->Update($arAlreadyBxInterestIndex[$XML_ID]["ID"],$arFields);            
        }
    }
    $arInterestIndex = $arAlreadyBxInterestIndex;
    // Чистим от предыдущих значений 
    /*
    $resPropertiesEnum = CIBlockPropertyEnum::GetList(
        array(), array( "IBLOCK_ID"=>CATALOG_IB_ID,
        "PROPERTY_ID"=>INTEREST_PROPERTY_ID)
    );
    while($arPropertyEnum = $resPropertiesEnum->getNext())
        CIBlockPropertyEnum::Delete($arPropertyEnum["ID"]);
    // Добавляем значения флагов из XML
    $arInterestIndex = array();
    foreach($arInterests as $arInt){
        $nIntID = CIBlockPropertyEnum::Add(array(
            "PROPERTY_ID"   =>  INTEREST_PROPERTY_ID,
            "VALUE"         =>  $arInt["Наименование"],
            "XML_ID"        =>  $arInt["Ид"]
        ));
        $arInterestIndex[$arInt["Ид"]] = array(
            "NAME"  =>  $arInt["Наименование"],
            "ID"    =>  $nIntID
        );
    }
    */

    ///////////////////////////////////////////////////////////////////////
    ///                  Импортируем производителей
    ///////////////////////////////////////////////////////////////////////
    $manufacturersIndex = array();
    $objManufacturer = new CIBlockElement;
    foreach($arManufacturers as $arManufacturer){
        $manufacturersIndex[$arManufacturer["Ид"]] = $arManufacturer;
        $arManufacturerMain = array(
            "NAME"=>$arManufacturer["ПолноеНаименование"],
            "IBLOCK_ID"=>MANUFACTURER_IB_ID,
            "CODE"=>CUtil::translit($arManufacturer["ПолноеНаименование"],'ru',array()),
            "SORT"=>$arManufacturer["Сортировка"],
            "XML_ID"=>$arManufacturer["Ид"],
        );
        $arManufacturerProps = array(
            "SHORT_NAME"=>"КраткоеНаименование",
            "ADDRESS"=>"АдресФактический",
            "FULL_NAME"=>"ПолноеНаименование",
            "HOW_FIND"=>"КакПроехать",
            "COORDS"=>"КоординатыНаКарте",
            "DETAIL"=>"ДополнительнаяИнформация",
            "SCHEDULE"=>"ГрафикРаботы",
            "WEBSITE"=>"ОфициальныйСайт",
            "PHONE"=>"Телефон",
            "SCHEME"=>"СхемаПроезда",
        );
        // Ищем производителя с таким ID
        if(!$arIblockManufacturer = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID"=>MANUFACTURER_IB_ID,"XML_ID"=>$arManufacturer["Ид"]),
            false,
            array("nTopCount"=>1)
        )->getNext())
        // Если производителя нет - добавляем
        {
            $id = $objManufacturer->Add($arManufacturerMain);
        }
        else{
            $id = $arIblockManufacturer["ID"];
            $objManufacturer->Update($id, $arManufacturerMain);
        }
        $manufacturersIndex[$arManufacturer["Ид"]]["IBLOCK_ID"] = MANUFACTURER_IB_ID;
        $manufacturersIndex[$arManufacturer["Ид"]]["ID"] = $id;
        foreach($arManufacturerProps as $propertyCode=>$propertyValue)
                CIBlockElement::SetPropertyValueCode($id,$propertyCode,$arManufacturer[$propertyValue]);        
    }
    
    $CATALOG_IBLOCK_ID = CATALOG_IB_ID;
    
    ///////////////////////////////////////////////////////////////////////
    ///                     Импортируем разделы
    ///////////////////////////////////////////////////////////////////////
    $sectionsIndex = array();
    foreach($arGroups as $arGroup){
        $arFields = array(
            "NAME"      => $arGroup["Наименование"],

//            "ACTIVE"    => $arGroup["Включен"]=='Да'?"Y":"N",
            //"ACTIVE"    => preg_match("#Транспорт#",$arGroup["Наименование"])?"N":"Y",
            "SORT"      => $arGroup["Сортировка"],
            "IBLOCK_ID" => $CATALOG_IBLOCK_ID,
            "ACTIVE"    => $arGroup["Включен"]=='Нет'?"N":"Y",
            "XML_ID"    => $arGroup["Ид"],
        );
        // Транслитируем название к коду раздела
        $arFields["CODE"] = CUtil::translit($arGroup["Наименование"], "ru", 
            array("replace_space"=>"-", "replace_other"=>"-")
        );
        
        // Запрашиваем раздел по его XMLID
        $res = CIBlockSection::GetList(array(),
            array("XML_ID"=>$arGroup["Ид"],"IBLOCK_ID"=>$CATALOG_IBLOCK_ID)
        );

        // Если раздел есть - обновляем, нет - добавляем
        $objIBlockSection = new CIBlockSection;
        if(!$existsSection = $res->GetNext()){
            // Добавляем раздел
            $sectionId = $objIBlockSection->Add($arFields);
            $sectionsIndex[$arGroup["Ид"]] = $sectionId;
        }
        else{
            // Обновляем раздел
            $sectionsIndex[$arGroup["Ид"]] = $existsSection["ID"];
            $objIBlockSection->Update($existsSection["ID"], $arFields);
        }
    }

    ///////////////////////////////////////////////////////////////////////
    ///                     Импортируем товары
    ///////////////////////////////////////////////////////////////////////
    $productsIndex = array();
    $productsIndexDetail = array();

    // Составляем справочник флагов
    $ENUM = array();
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>CATALOG_IB_ID));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUM[$data["PROPERTY_CODE"]]))
            $ENUM[$data["PROPERTY_CODE"]] = array();
        $ENUM[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
    }
    
    // Проходим по каждому товару в XML
    foreach($arProducts as $arProduct){
        $arProduct["Реквизиты"] = array();
        if(!isset($arProduct["ЗначенияРеквизитов"]["ЗначениеРеквизита"][0]))
            $arProduct["ЗначенияРеквизитов"]["ЗначениеРеквизита"] = array(
                $arProduct["ЗначенияРеквизитов"]["ЗначениеРеквизита"]
            );

        foreach($arProduct["ЗначенияРеквизитов"]["ЗначениеРеквизита"] as $arReq){
            $arProduct["Реквизиты"][$arReq["Наименование"]] = $arReq["Значение"];
        }
        

        ////////////////////////////////////////////////////////////////////////
        // Основные поля инфоблока
        ////////////////////////////////////////////////////////////////////////
        $arFields = array();
        $arFields["NAME"]               = $arProduct["Наименование"];
        $arFields["ACTIVE"]             = $arProduct["Включен"]=='Да'?"Y":"N";
        $arFields["SORT"]               = $arProduct["Сортировка"];
        $arFields["IBLOCK_ID"]          = $CATALOG_IBLOCK_ID;
        $arFields["XML_ID"]             = $arProduct["Ид"];
        $arFields["PREVIEW_TEXT"]       = $arProduct["Описание"];
        $arFields["DETAIL_TEXT"]        = $arProduct["Описание"];
        $arFields["PREVIEW_TEXT_TYPE"]  = 'html';
        $arFields["DETAIL_TEXT_TYPE"]   = 'html';
        
        // Назначем товару группу
        if(isset($sectionsIndex[$arProduct["Группы"]["Ид"]])){
            $arFields["IBLOCK_SECTION_ID"]  = $sectionsIndex[$arProduct["Группы"]["Ид"]];
            $arFields["SECTION_ID"]         = $arFields["IBLOCK_SECTION_ID"];
        }
        else{
            $arFields["IBLOCK_SECTION_ID"] = true;
            $arFields["SECTION_ID"] = true;
        }

        // Формируем код товара из названия и первой части XMLID (название иногда совпадает)
        $code = explode("-",$arProduct["Ид"]);
        $code = $code[0];
        $arFields["CODE"]       = CUtil::translit(
            $arProduct["Наименование"], 
            "ru", 
            array("replace_space"=>"-", "replace_other"=>"-")
        )."-".$code;

        // Запрашиваем, есть товар с таким кодом
        $res = CIBlockElement::GetList(array(),array(
            "IBLOCK_ID"=>$CATALOG_IBLOCK_ID,
            "XML_ID"=>$arProduct["Ид"]
        ));

        $objIBlockElement = new CIBlockElement;
        // Если товар уже есть - получаем информацию о нём и обновляем
        // Если нет - добавляем
        if(!$existsElement = $res->GetNext()){
            // Добавляем товар
            if(!$elementId = $objIBlockElement->Add($arFields)){
                echo "<pre>";
                print_r($arFields);
                print_r($objIBlockElement);
                die;
            }
            $productsIndex[$arProduct["Ид"]] = $elementId;
        }
        else{
            // Обновляем товар
            $productsIndex[$arProduct["Ид"]] = $existsElement["ID"];
            $elementId = $existsElement["ID"];
            $objIBlockElement->Update($existsElement["ID"], $arFields);
        }
        
        // Если в XML у товара заполнен тег "Картинка"
        if($arProduct["Картинка"]){
            $arFields = array();
            $picturePath = isset($arProduct["Картинка"][0])?$arProduct["Картинка"][0]:'';
            // Если у товара есть картинка - пробуем обновить её
            if($existsElement["DETAIL_PICTURE"] && $picturePath){
                $res = CFile::GetByID($existsElement["DETAIL_PICTURE"]);
                $existsFileInfo = $res->GetNext();
                // Получаем информацию о загруженном файле
                //$picturePath = mb_convert_encoding($picturePath, "utf-8", "cp866");
                $picturePath = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$picturePath;
                $downlodedFileInfo = CFile::MakeFileArray($picturePath);
                // Если размер не совпадает - грузим новый
                if($existsFileInfo["FILE_SIZE"]!=$downlodedFileInfo["size"]){
                    CFile::Delete($existsElement["DETAIL_PICTURE"]);
                    $arFields["DETAIL_PICTURE"] = $arFields["PREVIEW_PICTURE"] = $downlodedFileInfo;
                    $objIBlockElement->Update($elementId, $arFields);
                }
            }
            // Если у товара нет картинки - добавляем её
            elseif(!$existsElement["DETAIL_PICTURE"] && $picturePath){
                // В ZIP кириллица нуждается в перекодировании
                //$picturePath = mb_convert_encoding($picturePath, "utf-8", "cp866");
                $picturePath = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$picturePath;
                $arFields["DETAIL_PICTURE"] = $arFields["PREVIEW_PICTURE"] = CFile::MakeFileArray($picturePath);
                $objIBlockElement->Update($elementId, $arFields);
            }
            else{
            }
        }

        $productsIndexDetail[$arProduct["Ид"]] = $arProduct;

        /////////////////// Устанавливаем свойства товара //////////////////////
        // Производитель
        $arManufactersTags = array(
            "ПолноеНаименование"        =>"Название::text",
            "АдресФактический"          =>"Адрес::text",
            "ГрафикРаботы"              =>"График работы::text",
            "ОфициальныйСайт"           =>"Сайт::url",
            "КакПроехать"               =>"Как проехать::text",
            "КоординатыНаКарте"         =>"Координаты::geo",
            "СхемаПроезда"              =>"Схема проезда::text",
            "ДополнительнаяИнформация"  =>"Дополнительная информаци::text"
        );
        $arProperties["MANUFACTURER"]   = base64_encode(serialize($manufacturersIndex[$arProduct["Производитель"]["Ид"]]));
        // Привязка к инфоблоку
        $arProperties["MANUFACTURER_LINK"] = $manufacturersIndex[$arProduct["Производитель"]["Ид"]]["ID"];
        // Срок исполнения
        $arProperties["DAYS_TO_EXPIRE"] = isset($arProduct["СрокИсполнения"])?intval($arProduct["СрокИсполнения"]):0;
        // Базовая единица
        $arProperties["QUANT"]          = $arProduct["БазоваяЕдиница"]["@attributes"]["НаименованиеПолное"];
        // Минимальная цена
        $arProperties["MINIMUM_PRICE"]      = $arProduct["Баллы"];
        // Максимальная цена
        $arProperties["MAXIMUM_PRICE"]      = $arProduct["Баллы"];
        // Максимальная цена
        $arProperties["MAXIMUM_PRICE"]      = $arProduct["Баллы"];
        // Возможность отмены
        $arProperties["CANCEL_ABILITY"]     = 
            $arProduct["ВозможностьОтмены"]=='Да'?$ENUM["CANCEL_ABILITY"]["да"]:0;
        // Отключатьб при нулевом остатке
        $arProperties["HIDE_IF_ABSENT"]     = 
            $arProduct["ОтключатьБезОстатка"]=='Да'?$ENUM["HIDE_IF_ABSENT"]["да"]:0;
        // Новинка
        $arProperties["NEWPRODUCT"] = 
            $arProduct["Новинка"]=='Да'?$ENUM["NEWPRODUCT"]["да"]:0;
        // Лидер продаж
        $arProperties["SALELEADER"] = 
            $arProduct["ЛидерПродаж"]=='Да'?$ENUM["SALELEADER"]["да"]:0;
        // Лидер продаж
        $arProperties["SPECIALOFFER"] = 
            $arProduct["ДоступенПоАкции"]=='Да'?$ENUM["SPECIALOFFER"]["да"]:0;
        $arProperties["PROMOCODE"] = 
            $arProduct["Промокод"]=='Да'?$ENUM["PROMOCODE"]["да"]:0;
        // Вычисляем массив хотелок
        $arIwantIds = array();
        if($arProduct["Хотелки"]["Хочу"]){
            if(!isset($arProduct["Хотелки"]["Хочу"][0]))
                $arProduct["Хотелки"]["Хочу"] = array($arProduct["Хотелки"]["Хочу"]);
            $tmp = $arProduct["Хотелки"]["Хочу"];
            foreach($tmp as $t) 
                if(
                    isset($t["@attributes"]["Ид"]) 
                    &&
                    $arIwantIndex[$t["@attributes"]["Ид"]]["ID"]
                )
                $arIwantIds[] = $arIwantIndex[$t["@attributes"]["Ид"]]["ID"];
        }
        // Вычисляем массив интересов
        $arInterestsIds = array();
        if($arProduct["Интересы"]["Интересуюсь"]){
            if(!isset($arProduct["Интересы"]["Интересуюсь"][0]))
                $arProduct["Интересы"]["Интересуюсь"] = array($arProduct["Интересы"]["Интересуюсь"]);
            $tmp = $arProduct["Интересы"]["Интересуюсь"];
            foreach($tmp as $t) 
                if(
                    isset($t["@attributes"]["Ид"]) 
                    &&
                    $arInterestIndex[$t["@attributes"]["Ид"]]["ID"]
                )
                $arInterestsIds[] = $arInterestIndex[$t["@attributes"]["Ид"]]["ID"];
        }
        // Хочу
        $arProperties["WANTS"] = $arIwantIds;
        // Интересуюсь
        $arProperties["INTERESTS"] = $arInterestsIds; 
        // Тип поощрений
        $arProperties["TYPES"] = $ENUM["TYPES"][$arProduct["ТипПоощрения"]];
        // Правила получения
        $arProperties["RECEIVE_RULES"] = $arProduct["ПравилаПолученияЗаказа"];
        // Правила отмены
        $arProperties["CANCEL_RULES"] = $arProduct["ПравилаОтменыЗаказа"];
        // Артикул
        $arProperties["ARTNUMBER"] = $arProduct["Артикул"];
        // Отправлять сертификат
        $arProperties["SEND_CERT"]     = 
            $arProduct["ОтправлятьСертификат"]=='Да'?$ENUM["SEND_CERT"]["да"]:0;
        // Месячный лимит
        $arProperties["MON_LIMIT"] = $arProduct["МесячныйЛимит"];
        // Месячный лимит
        $arProperties["RATING_LIMIT"] = $arProduct["РейтингДляПокупки"];
        // Невыбираемый остаток
        $arProperties["STORE_LIMIT"] = $arProduct["НевыбираемыйОстаток"];
        // Дата сокрытия
        $tmp = date_parse($arProduct["ДатаОтключения"]);
        if(isset($tmp["error_count"]) && !$tmp["error_count"]){
            $arProperties["HIDE_DATE"] = 
                $tmp["day"]
                .".".$tmp["month"]
                .".".$tmp["year"]
                ." ".($tmp["hour"]?$tmp["hour"]:"00")
                .":".($tmp["minute"]?$tmp["minute"]:"00")
                .":".($tmp["second"]?$tmp["second"]:"00")
            ;
        }
        else{
            $arProperties["HIDE_DATE"] =  "";
        }

         
        // Дата мероприятия
        if(trim($arProduct["ДатаМероприятия"])){
            $tmp = date_parse($arProduct["ДатаМероприятия"]);
            $arProduct["ДатаМероприятия"] = date(
                "d.m.Y",mktime(0,0,0,$tmp["month"],$tmp["day"],$tmp["year"])
            );
            $arProperties["PERFOMANCE_DATE"] = $arProduct["ДатаМероприятия"];
        }
        else{
            $arProperties["PERFOMANCE_DATE"] = '';
        }
        
        // Использовать по дату
        if(trim($arProduct["ИспользоватьПоДату"])){
            $tmp2 = date_parse($arProduct["ИспользоватьПоДату"]);
            $arProduct["ИспользоватьПоДату"] = date(
                "d.m.Y",mktime(0,0,0,$tmp2["month"],$tmp2["day"],$tmp2["year"])
            );
            $arProperties["USE_BEFORE_DATE"] = $arProduct["ИспользоватьПоДату"];
        }
        else{
            $arProperties["USE_BEFORE_DATE"] = '';
        }
        

        // Ещё одно название нужное только для того, чтобы иметь возможность в
        //них запутаться
        $arProperties["BUH_NAME"] = 
            isset($arProduct["Реквизиты"]["Полное наименование"])
            &&
            trim($arProduct["Реквизиты"]["Полное наименование"])
            ?
            $arProduct["Реквизиты"]["Полное наименование"]
            :
            $arProduct["Наименование"]
            ;

        foreach($arProperties as $propertyCode=>$propertyValue)
            CIBlockElement::SetPropertyValueCode($elementId,$propertyCode,$propertyValue);
        
        
        // Дополнительные изображения
        if(count($arProduct["Картинка"])){

            $arrFile = array();
            
            // Составляем индекс размеров файлов
            $check_prop_value = array();
            foreach($arProduct["Картинка"] as $value){
                $picturePath = mb_convert_encoding($value, "utf-8", "cp866");
                $picturePath = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$picturePath;
                $headers = CFile::MakeFileArray($picturePath);
                $check_prop_value[$headers["size"]] = $value; 
            }
            // Получаем размеры фотографий свойства MORE_PHOTO
            $res = CIBlockElement::GetProperty($CATALOG_IBLOCK_ID, $elementId,array(),array("CODE"=>"MORE_PHOTO"));
            while($photoItem = $res->GetNext()){
                $res1 = CFile::GetByID($photoItem["VALUE"]);
                $localFileInfo = $res1->GetNext(); 
                if(isset(
                    $check_prop_value[$localFileInfo["FILE_SIZE"]]
                ))unset(
                    $check_prop_value[$localFileInfo["FILE_SIZE"]]
                );
            }

            // Если хоть одн изображение не совпадает по размерам
            // меняем весь список изображений
            if(count($check_prop_value)){
                // Удаляем все файлы
                $res = CIBlockElement::GetProperty($CATALOG_IBLOCK_ID, $elementId,array(),array("CODE"=>"MORE_PHOTO"));            
                while($photoItem = $res->GetNext())CFile::Delete($photoItem["VALUE"]);
                // Делаем массив для добавления
                foreach($arProduct["Картинка"] as $img){
                    $picturePath = mb_convert_encoding($img, "utf-8", "cp866");
                    $picturePath = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$picturePath;
                    $arrFile[] = array(
                        "VALUE"=>CFile::MakeFileArray($picturePath),
                        "DESCRIPTION"=>""
                    );
                }
                CIBlockElement::SetPropertyValuesEx(
                    $elementId, $CATALOG_IBLOCK_ID, 
                    array('MORE_PHOTO' => $arrFile)
                );
            }


            
        }
        

    }
