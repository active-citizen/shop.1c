<?php
    
    // Загружаем XML
    $xmlImport = file_get_contents($uploadDir.$importFilename);
    $obImport = simplexml_load_string($xmlImport, "SimpleXMLElement" );
    
    // Преобразуем объект к массиву
    $arImport = json_decode(json_encode((array)$obImport), TRUE);        
    
    // Получаем из XML массив групп товаров
    $arGroups = $arImport["Классификатор"]["Группы"]["Группа"];
    // Получаем из XML массив производителей
    $arManufacturers = $arImport["Классификатор"]["Производители"]["Производитель"];
    // Получаем из XML массив товаров
    $arProducts = $arImport["Каталог"]["Товары"]["Товар"];


    // Приводим картинки к массивному виду
    foreach($arProducts as $k=>$arProduct)
        if(isset($arProducts[$k]["Картинка"]) && !is_array($arProducts[$k]["Картинка"]))
            $arProducts[$k]["Картинка"] = array($arProducts[$k]["Картинка"]);
    
    ///////////////////////////////////////////////////////////////////////
    ///                  Импортируем производителей
    ///////////////////////////////////////////////////////////////////////
    $manufacturersIndex = array();
    $arManufacturerIblock = CIBlock::GetList(array(),array("CODE"=>"manuacturers"))->GetNext();
    $objManufacturer = new CIBlockElement;
    foreach($arManufacturers as $arManufacturer){
        $manufacturersIndex[$arManufacturer["Ид"]] = $arManufacturer;
        $arManufacturerMain = array(
            "NAME"=>$arManufacturer["ПолноеНаименование"],
            "IBLOCK_ID"=>$arManufacturerIblock["ID"],
            "CODE"=>CUtil::translit($arManufacturer["ПолноеНаименование"],'ru',array()),
            "SORT"=>$arManufacturer["Сортировка"],
            "XML_ID"=>$arManufacturer["Ид"],
        );
        $arManufacturerProps = array(
            "SHORT_NAME"=>"КраткоеНаименование",
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
            array("IBLOCK_CODE"=>"manuacturers","XML_ID"=>$arManufacturer["Ид"]),
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
        $manufacturersIndex[$arManufacturer["Ид"]]["IBLOCK_ID"] = $arManufacturerIblock["ID"];
        $manufacturersIndex[$arManufacturer["Ид"]]["ID"] = $id;
        foreach($arManufacturerProps as $propertyCode=>$propertyValue)
            CIBlockElement::SetPropertyValueCode($id,$propertyCode,$arManufacturer[$propertyValue]);        
    }
    
    $CATALOG_IBLOCK_ID = 2;
    
    ///////////////////////////////////////////////////////////////////////
    ///                     Импортируем разделы
    ///////////////////////////////////////////////////////////////////////
    $sectionsIndex = array();
    foreach($arGroups as $arGroup){
        $arFields = array(
            "NAME"      => $arGroup["Наименование"],
            "ACTIVE"    => "Y",
            "SORT"      => $arGroup["Сортировка"],
            "IBLOCK_ID" => $CATALOG_IBLOCK_ID,
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
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>2));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUM[$data["PROPERTY_CODE"]]))$ENUM[$data["PROPERTY_CODE"]] = array();
        $ENUM[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
    }
    

    // Проходим по каждому товару в XML
    foreach($arProducts as $arProduct){
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
                $picturePath = mb_convert_encoding($picturePath, "utf-8", "cp866");
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
                $picturePath = mb_convert_encoding($picturePath, "utf-8", "cp866");
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

        /*
        foreach($arManufactersTags as $tagName=>$fieldName){
            // Пропускае пустые
            if(!trim($manufacturersIndex[$arProduct["Производитель"]["Ид"]][$tagName]))continue;
            // Определяем имя поля и тип
            $nameArgs = explode("::",$fieldName);
            $name = $nameArgs[0];
            $type = isset($nameArgs[1]) && $nameArgs[1]?$nameArgs[1]:text;
            // В зависимости от типа что-то выводим 
            switch($type){
                case 'url':
                    $arProperties["MANUFACTURER"].=
                        '<tr><th>'.$name.'</th><td><a href="'.$manufacturersIndex[$arProduct["Производитель"]["Ид"]][$tagName].'">'
                            .$manufacturersIndex[$arProduct["Производитель"]["Ид"]][$tagName]
                        .'</a></td></tr>';
                break;
                default:
                    $arProperties["MANUFACTURER"].=
                        '<tr><th>'.$name.'</th><td>'
                            .$manufacturersIndex[$arProduct["Производитель"]["Ид"]][$tagName]
                        .'</td></tr>';
                break;
            }
            
        }
        $arProperties["MANUFACTURER"]   .= '</table>';
        */
        
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
        // Новинка
        $arProperties["NEWPRODUCT"] = 
            $arProduct["Новинка"]=='Да'?$ENUM["NEWPRODUCT"]["да"]:0;
        // Лидер продаж
        $arProperties["SALELEADER"] = 
            $arProduct["ЛидерПродаж"]=='Да'?$ENUM["SALELEADER"]["да"]:0;
        // Лидер продаж
        $arProperties["SPECIALOFFER"] = 
            $arProduct["ДоступенПоАкции"]=='Да'?$ENUM["SPECIALOFFER"]["да"]:0;
        // Интересуюсь
        $arProperties["INTERESTS"] = $ENUM["INTERESTS"][$arProduct["Интересуюсь"]];
        // Хочу
        $arProperties["WANTS"] = $ENUM["WANTS"][$arProduct["Хочу"]];
        // Тип поощрений
        $arProperties["TYPES"] = $ENUM["TYPES"][$arProduct["ТипПоощрения"]];
        // Правила получения
        $arProperties["RECEIVE_RULES"] = $arProduct["ПравилаПолученияЗаказа"];
        // Правила отмены
        $arProperties["CANCEL_RULES"] = $arProduct["ПравилаОтменыЗаказа"];
        // Артикул
        $arProperties["ARTNUMBER"] = $arProduct["Артикул"];

            
            
            

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
