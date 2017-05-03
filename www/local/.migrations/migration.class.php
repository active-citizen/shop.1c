<?php
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/bitrix/modules/main/include/prolog_before.php"
);

/**
    Migrations support methods
*/
class MigrationToolkit{
    
    var $error = '';
    
    
    function __construct(){
    }

    /**
        Добавление/редактирование шаблона в БД битрикса
        (необходимые файлы в local/templates надо добавлять руками)
    */
    function editTemplate($arArgs = array()){
        $arParams = array(
            "TEMPLATE"  => 
                isset($arArgs["TEMPLATE"])?$arArgs["TEMPLATE"]:"noname",
            "SITE_ID"   => 
                isset($arArgs["SITE_ID"])?$arArgs["SITE_ID"]:SITE_ID,
            "SORT"      => 
                isset($arArgs["SORT"])?$arArgs["SORT"]:500,
            "CONDITION" => 
                isset($arArgs["CONDITION"])?$arArgs["CONDITION"]:""
        );

        // Получаем список шаблонов сайта 
        $resTemplates = CSite::GetTemplateList($arParams["SITE_ID"]);

        $arTemplates = array();
        // Для каждого шаблона основного сайта
        $bTemplateFlag = false;
        while($arTemplate = $resTemplates->Fetch()){
            if($arTemplate["TEMPLATE"]==$arParams["TEMPLATE"]){
                $arTemplate["SORT"] = $arParams["SORT"];
                $arTemplate["CONDITION"] = $arParams["CONDITION"];
                $bTemplateFlag = true;
            }
            $arTemplates[] = $arTemplate;
        }
        $arParams["ID"] = $arTemplate["ID"] + 1;
        if(!$bTemplateFlag)$arTemplates[] = $arParams; 

        $obSite = new CSite();
        // Обновляем список шаблонов для сайта
        $obSite->Update(
            $arParams["SITE_ID"], 
            array(
                'ACTIVE' => "Y",
                'TEMPLATE'=>$arTemplates
            )
        );               
        
        return true;    
    }

    /**
        Работа с инфоблоком: создание/редактирование(типом, инфоблоком, 
        свойствами)
        
        Все несуществующие элементы создаются. 
        
        Пример входных параметров инфоблока
        
            $ibData = array(
                // Тпы инфоблока
                array(
                    "ID"        =>  "counters",
                    "SECTIONS"  =>  "N",
                    "LANG"      =>  array(
                        "ru"        =>  array(
                            "NAME"          =>  "Счетчики",
                            "SECTION_NAME"  =>  "Счетчики",
                            "ELEMENT_NAME"  =>  "Счетчики"
                        ),
                        "en"        =>  array(
                            "NAME"          =>  "Counters",
                            "SECTION_NAME"  =>  "Counters",
                            "ELEMENT_NAME"  =>  "Counters"
                        ),
                    ),
                    "IBLOCKS"=>array(
                        // Инфоблок
                        array(
                            "NAME"=>"Мои желания",
                            "SITE_ID"=>"s1",
                            "CODE"=>"whishes",
                            "GROUP_ID"=>array("1"=>"X","2"=>"X"),
                            // Свойства инфоблока
                            "PROPERTIES"=>array(
                                "WISH_PRODUCT" =>array(
                                    "NAME"=>"ID желаемого товара",
                                    "PROPERTY_TYPE"=>"N",
                                ),
                                "WISH_TYPE" =>array(
                                    "NAME"=>"Тип желания",
                                    "PROPERTY_TYPE"=>"L",
                                    "VALUES"    =>  array(
                                        array(
                                            "SORT"=>"100",
                                            "VALUE"=>"Сейчас",
                                            "XML_ID"=>"NOW"
                                        ),
                                        array(
                                            "SORT"=>"200",
                                            "VALUE"=>"Быстро",
                                            "XML_ID"=>"FAST"
                                        ),
                                    )
                                ),
                            ),
                            // Элементы(наполнение)
                            "ITEMS"=>array(
                                array(
                                    "NAME"=>"Желание 1",
                                    "PROPERTIES"=>array(
                                        "WISH_USER"     =>  1,
                                        "WISH_PRODUCT"  =>  1
                                    )
                                ),
                                array(
                                    "NAME"=>"Желание 2",
                                    "PROPERTIES"=>array(
                                        "WISH_USER"     =>  1,
                                        "WISH_PRODUCT"  =>  2
                                    )
                                ),
                            )
                        ),
                        // Инфоблок
                        array(
                            "NAME"=>"Мои оценки",
                            "SITE_ID"=>"s1",
                            "CODE"=>"marks",
                            "GROUP_ID"=>array("1"=>"X","2"=>"X"),
                            // Свойства инфоблока
                            "PROPERTIES"=>array(
                                "MARK_USER" =>array(
                                    "NAME"=>"ID оценивающего пользователя",
                                    "PROPERTY_TYPE"=>"N",
                                ),
                            )
                        ),
                    )
                )
            );        
    */
    function editIblock(
        $ibData   //!< параметры инфоблока
    ){
        \CModule::IncludeModule("iblock");
        $ibTypeObj = new \CIBlockType;
        $ibObj = new \CIBlock;
        $ibPropObj = new \CIBlockProperty;
        $ibElemObj = new \CIBlockElement;
        foreach($ibData as $ibType){
            $data = $ibType;
            unset($data["IBLOCKS"]);
            $res = \CIBlockType::GetList(array(),array("ID"=>$ibType["ID"]));
            if(!$ibTypeElem = $res->getNext())
                $ibTypeObj->Add($data);
            else
                $ibTypeObj->Update($ibTypeElem["ID"],$data);
                
            
            foreach($ibType["IBLOCKS"] as $ib){
                $data = $ib;
                unset($data["PROPERTIES"]);
                unset($data["ITEMS"]);
                $data["IBLOCK_TYPE_ID"] = $ibType["ID"];
                $res = \CIBlock::GetList(array(),array("CODE"=>$ib["CODE"]));
                $nIblockId = 0;
                if(!$ibElem = $res->getNext())
                    $nIblockId = $ibObj->Add($data);
                else
                    $ibObj->Update($nIblockId = $ibElem["ID"],$data);
                $res = \CIBlock::GetList(array(),array("CODE"=>$ib["CODE"]));
                $ibElem = $res->getNext();
                
                foreach($ib["PROPERTIES"] as $CODE => $ibProp){
                    $ibProp["IBLOCK_ID"] = $ibElem["ID"];
                    $ibProp["CODE"]=$CODE;
                    $res = \CIBlockProperty::GetList(
                        array(),array("CODE"=>$ibProp["CODE"])
                    );
                    if(!$ibPropElem = $res->getNext()){
                        $ibPropObj->Add($ibProp);
                    }
                    else{
                        $ibPropObj->Update($ibPropElem["ID"],$ibProp);
                    }
                }
                
                foreach($ib["ITEMS"] as $item){
                    $item["IBLOCK_ID"] = $ibElem["ID"];
                    $res = \CIBlockElement::GetList(
                        array(),
                        array(
                            "IBLOCK_ID"=>$item["IBLOCK_ID"],
                            "NAME"=>$item["NAME"]
                        ),
                        false,
                        array("nTopCount"=>1)
                    );
                    
                    if(!$ibElemElem = $res->getNext()){
                        $ID = $ibElemObj->Add($item);
                        foreach(
                            $item["PROPERTIES"] as $prop_code=>$prop_value
                        ){
                            if(
                                isset(
                                    $ib["PROPERTIES"][$prop_code]
                                        ["PROPERTY_TYPE"]
                                ) 
                                && 
                                $ib["PROPERTIES"][$prop_code]
                                    ["PROPERTY_TYPE"]=='F'
                            ){
                                $arFileInfo = \CFile::MakeFileArray($prop_value);
                                $arFileInfo["MODULE_ID"] = "iblock";
                                $arFileInfo["description"] = "описание файла";
                                $arFileInfo["del"] = "N";
                                $arFileInfo["type"] = 'image/jpeg';
                                if($arFileInfo)
                                    $prop_value = \CFile::SaveFile(
                                        $arFileInfo,"migrated"
                                    );
                            }

                            if(
                                isset(
                                    $ib["PROPERTIES"][$prop_code]
                                        ["PROPERTY_TYPE"]
                                ) 
                                && 
                                $ib["PROPERTIES"][$prop_code]
                                    ["PROPERTY_TYPE"]=='L'
                            ){
                                $arProp = CIBlockProperty::GetPropertyEnum(
                                    $prop_code,
                                    array(),
                                    array(
                                        "IBLOCK_ID"=>$item["IBLOCK_ID"],
                                        "VALUE"=>$prop_value
                                    )
                                )->GetNext();
                                $prop_value = $arProp["ID"];
                            }

                            \CIBlockElement::SetPropertyValues(
                                $ID,$item["IBLOCK_ID"],$prop_value,$prop_code
                            );
                        }
                    }
                    else{
                        
                        $ibElemObj->Update($ibElemElem["ID"],$item);
                        foreach($item["PROPERTIES"] as $prop_code=>$prop_value){
                            if(
                                isset(
                                    $ib["PROPERTIES"][$prop_code]
                                        ["PROPERTY_TYPE"]
                                ) 
                                && 
                                $ib["PROPERTIES"][$prop_code]
                                    ["PROPERTY_TYPE"]=='F'
                            ){
                                $arFileInfo = \CFile::MakeFileArray($prop_value);
                                $arFileInfo["MODULE_ID"] = "iblock";
                                $arFileInfo["description"] = "описание файла";
                                $arFileInfo["del"] = "N";
                                if($arFileInfo)
                                    $prop_value = \CFile::SaveFile(
                                        $arFileInfo,"migrated"
                                    );
                            }
                            if(
                                isset(
                                    $ib["PROPERTIES"][$prop_code]
                                        ["PROPERTY_TYPE"]
                                ) 
                                && 
                                $ib["PROPERTIES"][$prop_code]
                                    ["PROPERTY_TYPE"]=='L'
                            ){
                                $arProp = CIBlockProperty::GetPropertyEnum(
                                    $prop_code,
                                    array(),
                                    array(
                                        "IBLOCK_ID"=>$item["IBLOCK_ID"],
                                        "VALUE"=>$prop_value
                                    )
                                )->GetNext();
                                $prop_value = $arProp["ID"];
                            }

                            \CIBlockElement::SetPropertyValues(
                                $ibElemElem["ID"],
                                $item["IBLOCK_ID"],
                                $prop_value,$prop_code
                            );
                        }
                        
                    }
                }
                
            }
        }
        
        return $nIblockId;
    
    }


    /**
        Установка элемента инфоблока. Есть - обновляем, нет - добавляем
        
    */
    function setItem(
        $nIblockId,     //!< ID инфоблока, куда добавляем элемент
        $sSectionCode,  //!< Код родительского раздела (пустой - в корень)
        $arFields,      //!< Массив добавляемых полей
        $sType = 'Section',   //!< Тип добавляемого объекта(Section or Element)  
        $arProperties = array(),    //!< Свойства массив массивов
        $arUFs = array()  //!< Пользовательские свойства
        
    ){
        $sType = mb_strtolower($sType);
        $nSectionId = 0;
        // CODE и NAME должны бать указаны
        if(!trim($arFields["CODE"]) || !trim($arFields["NAME"])){
            $this->error = "CODE и NAME должны бать определены";
            return false;
        }
        
        unset($arFields["IBLOCK_ID"]);
        unset($arFields["SECTION_ID"]);
        unset($arFields["IBLOCK_SECTION_ID"]);
        $arFields["IBLOCK_ID"] = $nIblockId;
        if(trim($sSectionCode)){
            $arSection = CIBlockSection::GetList(array(),array(
                "IBLOCK_ID"=>$nIblockId,
                "CODE"=>$sSectionCode,
            ))->GetNext();
            $nSectionId = $arSection["ID"];
        }
        if($nSectionId)$arFields["SECTION_ID"] = 
            $arFields["IBLOCK_SECTION_ID"] = $nSectionId;

        switch($sType){
            case "section":
                $objIBlockSection = new CIBlockSection;
                if($arSection = $objIBlockSection->GetList(
                    array(),
                    array("IBLOCK_ID"=>$nIblockId,"CODE"=>$arFields["CODE"]),
                    false,
                    array("nTopCount"=>1),
                    array("ID")
                )->GetNext()){
                    $objIBlockSection->Update($arSection["ID"],$arFields);
                }
                else{
                    if(!$nSectionId = $objIBlockSection->Add($arFields)){
                        $this->error = 'Create section error:'.
                            $objIBlockSection->LAST_ERROR;
                        return false;
                    }
                }
            break;
            case "element":
            break;
        }
        return true;
    }
    
    /*
        Добавление к инфоблоку пользовательского поля
        
    */
    function setIblockSectionUF(
        $nIblockId, //!<
        $sUFName,   //!< Название пользовательского поля
        $sUFType,   //!< Тип пользовательсного поля 
        // (enumeration, double, integer, boolean, string, file, video, 
        // datetime, iblock_section, iblock_element, string_formatted, crm,
        // crm_status
        $sLabelRU   =  '',
        $sLabelEN   =  '',
        $arFieldsParams=array() //!< Дополнительные поля метода CUserTypeEntity->Add
    ){
        $arFieldsSearch = array(
            "ENTITY_ID"     =>  "IBLOCK_".$nIblockId."_SECTION",
            "FIELD_NAME"    =>  $sUFName
        );
        $arFields = array_merge($arFieldsSearch, $arFieldsParams);
        $arFields["EDIT_FORM_LABEL"] = array(
            "ru"    =>  $sLabelRU,
            "en"    =>  $sLabelEN
        );
        $arFields["USER_TYPE_ID"] = $sUFType;

        
        if(!$arUserField =  CUserTypeEntity::GetList(
            array(),
            $arFieldsSearch
        )->GetNext()){
            $obUserField  = new CUserTypeEntity;
            if(!$nUFId = $obUserField->Add($arFields)){
                $this->error = "UF set error: :".$obUserField->LAST_ERROR;
                return false;
            }
        }
        else{
            $obUserField  = new CUserTypeEntity;
            $obUserField->Update($arUserField["ID"], $arFields);
        }
        return true;
    }
    
    /*
        Добавление к пользователю пользовательского поля
        
    */
    function setUserUF(
        $sUFName,   //!< Название пользовательского поля
        $sUFType,   //!< Тип пользовательсного поля 
        // (enumeration, double, integer, boolean, string, file, video, 
        // datetime, iblock_section, iblock_element, string_formatted, crm,
        // crm_status
        $sLabelRU   =  '',
        $sLabelEN   =  '',
        $arFieldsParams=array() //!< Дополнительные поля метода CUserTypeEntity->Add
    ){
        $arFieldsSearch = array(
            "ENTITY_ID"     =>  "USER",
            "FIELD_NAME"    =>  $sUFName
        );
        $arFields = array_merge($arFieldsSearch, $arFieldsParams);
        $arFields["EDIT_FORM_LABEL"] = array(
            "ru"    =>  $sLabelRU,
            "en"    =>  $sLabelEN
        );
        $arFields["USER_TYPE_ID"] = $sUFType;

        
        if(!$arUserField =  CUserTypeEntity::GetList(
            array(),
            $arFieldsSearch
        )->GetNext()){
            $obUserField  = new CUserTypeEntity;
            if(!$nUFId = $obUserField->Add($arFields)){
                $this->error = "UF set error: :".$obUserField->LAST_ERROR;
                return false;
            }
        }
        else{
            $obUserField  = new CUserTypeEntity;
            $obUserField->Update($arUserField["ID"], $arFields);
        }
        return true;
    }

}

