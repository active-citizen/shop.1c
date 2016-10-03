<?php
    $xmlImport = file_get_contents($uploadDir.$importFilename);
    $obImport = simplexml_load_string(
        $xmlImport, "SimpleXMLElement" 
    );
    
    $arImport = json_decode(json_encode((array)$obImport), TRUE);        
    
    $arGroups = $arImport["Классификатор"]["Группы"]["Группа"];
    $arManufacturers = 
        $arImport["Классификатор"]["Производители"]["Производитель"];
    $arProducts = $arImport["Каталог"]["Товары"]["Товар"];
    
    // Приводим картинки к массивному виду
    foreach($arProducts as $k=>$arProduct)
        if(
            isset($arProducts[$k]["Картинка"]) 
            && !is_array($arProducts[$k]["Картинка"])
        )$arProducts[$k]["Картинка"] = array($arProducts[$k]["Картинка"]);
    
    ///////////////////////////////////////////////////////////////////////
    ///                  Импортируем производителей
    ///////////////////////////////////////////////////////////////////////
    $manufacturersIndex = array();
    foreach($arManufacturers as $arManufacturer){
        $manufacturersIndex[$arManufacturer["Ид"]] = $arManufacturer;
    }
    
    $CATALOG_IBLOCK_ID = 2;
    
    ///////////////////////////////////////////////////////////////////////
    ///                     Импортируем разделы
    ///////////////////////////////////////////////////////////////////////
    $sectionsIndex = array();
    foreach($arGroups as $arGroup){
        $arFields = array();
        $arFields["NAME"]       = $arGroup["Наименование"];
        $arFields["ACTIVE"]     = "Y";
        $arFields["SORT"]       = $arGroup["Сортировка"];
        $arFields["IBLOCK_ID"]  = $CATALOG_IBLOCK_ID;
        $arFields["XML_ID"]     = $arGroup["Ид"];
        
        $arFields["CODE"]       = CUtil::translit(
            $arGroup["Наименование"], 
            "ru", 
            array("replace_space"=>"-", "replace_other"=>"-")
        );

        $res = CIBlockSection::GetList(
            array(),
            array("XML_ID"=>$arGroup["Ид"],"IBLOCK_ID"=>$CATALOG_IBLOCK_ID)
        );

        $objIBlockSection = new CIBlockSection;
        if(!$existsSection = $res->GetNext()){
            $sectionId = $objIBlockSection->Add($arFields);
            $sectionsIndex[$arGroup["Ид"]] = $sectionId;
        }
        else{
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

    foreach($arProducts as $arProduct){
        
        $arFields = array();
        $arFields["NAME"]           = $arProduct["Наименование"];
        $arFields["ACTIVE"]         = $arProduct["Включен"]=='Да'?"Y":"N";
        $arFields["SORT"]           = $arProduct["Сортировка"];
        $arFields["IBLOCK_ID"]      = $CATALOG_IBLOCK_ID;
        $arFields["XML_ID"]         = $arProduct["Ид"];
        
        if(isset($sectionsIndex[$arProduct["Группы"]["Ид"]])){
            $arFields["IBLOCK_SECTION_ID"]  = $sectionsIndex[$arProduct["Группы"]["Ид"]];
            $arFields["SECTION_ID"]         = $arFields["IBLOCK_SECTION_ID"];
        }
        else{
            $arFields["IBLOCK_SECTION_ID"] = true;
            $arFields["SECTION_ID"] = true;
        }

        $code = explode("-",$arProduct["Ид"]);
        $code = $code[0];
        $arFields["CODE"]       = CUtil::translit(
            $arProduct["Наименование"], 
            "ru", 
            array("replace_space"=>"-", "replace_other"=>"-")
        )."-".$code;

        $res = CIBlockElement::GetList(array(),array(
            "IBLOCK_ID"=>$CATALOG_IBLOCK_ID,
            "XML_ID"=>$arProduct["Ид"]
        ));

        $objIBlockElement = new CIBlockElement;
        if(!$existsElement = $res->GetNext()){
            if(!$elementId = $objIBlockElement->Add($arFields)){
                echo "<pre>";
                print_r($objIBlockElement);
                die;
            }
            $productsIndex[$arProduct["Ид"]] = $elementId;
            
        }
        else{
            $productsIndex[$arProduct["Ид"]] = $existsElement["ID"];
            $elementId = $existsElement["ID"];
            $objIBlockElement->Update($existsElement["ID"], $arFields);
        }
        
        
        if($arProduct["Картинка"]){
            
            $res = $objIBlockElement->GetList(
                array(),
                array("ID"=>$elementId,"IBLOCK_ID"=>$CATALOG_IBLOCK_ID),
                false, array("nTopCount"=>1)
            );
            
            $currentElement = $res->GetNext();
            $arFields = array();
            $picturePath = isset($arProduct["Картинка"][0])?$arProduct["Картинка"][0]:'';

            if(
                $currentElement["DETAIL_PICTURE"]
                && $picturePath
            ){
            }
            elseif(
                !$currentElement["DETAIL_PICTURE"]
                && $picturePath
            ){
                $picturePath = mb_convert_encoding($picturePath, "utf-8", "cp866");
                $picturePath = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$picturePath;
                $arFields["DETAIL_PICTURE"] = CFile::MakeFileArray($picturePath);
                $arFields["PREVIEW_PICTURE"] = CFile::MakeFileArray($picturePath);
                $objIBlockElement->Update($elementId, $arFields);
                echo "<pre>$picturePath<br/>";
                print_r($arFields);
                print_r($currentElement);
                die;
            }
            else{
            }
            
            
            /*
            $res = CIBlockElement::GetProperty(
                $CATALOG_IBLOCK_ID,
                $elementId,
                array(),
                array("CODE"=>"PREVIEW_PICTURE")
            );
            
            // Если картинка существует
            if($pic = $res->GetNext()){
                echo "<pre>";
                print_r($pic);
                echo "</pre>";
                
            }
            else{
                
            }
            * */
            
            
            // Получаем размер 
            echo "<pre>";
            print_r($arProduct["Картинка"]);
            echo "</pre><hr/>";
        }

        $productsIndexDetail[$arProduct["Ид"]] = $arProduct;

        ////////// Устанавливаем свойства товара
        $arProperties["MANUFACTURER"]   = $manufacturersIndex[$arProduct["Производитель"]["Ид"]]["ПолноеНаименование"];
        $arProperties["QUANT"]          = $arProduct["БазоваяЕдиница"]["@attributes"]["НаименованиеПолное"];

        foreach($arProperties as $propertyCode=>$propertyValue){
            CIBlockElement::SetPropertyValueCode(
                $elementId,
                $propertyCode,
                $propertyValue
            );
        }

    }
