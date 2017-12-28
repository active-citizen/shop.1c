<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();



//if ($this->StartResultCache(false)) {

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogSection.class.php"
    );
    use AGShop\Catalog as Catalog;
    
    $objSection = new \Catalog\CCatalogSection;
    $arSections = $objSection->get([
        "ACTIVE"=>"Y",
        "ONLY_WITH_PRODUCTS"=>true,
        "ONLY_WITH_PRESENT_PRODUCTS"=>true
    ]);
    $arResult["CURRENT_SECTION"] = '';
    foreach($arSections as $nKey=>$arSection){
        if(isset($arIconsClasses[$arSection["CODE"]]))
            $arSections[$nKey]["CLASSNAME"]=$arIconsClasses[$arSection["CODE"]];
        else
            $arSections[$nKey]["CLASSNAME"]=$sSectionIconDefault;
        if(preg_match("#^/catalog/".$arSection["CODE"]."/#",$_SERVER["REQUEST_URI"])){
            $arSections[$nKey]["CURRENT"]=true;
            $arResult["CURRENT_SECTION"] = $arSection["CODE"];
        }
        
    }
    $arResult["SECTIONS"] = $arSections;
 
    /****** Зарефакторено **********
    // Получаем корневых разделов
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/catalog.lib.php");
    // Получаем теги для отображения в каталоге мобильного приложения

    $arResult["IN_CATALOG"] = false;
    if(preg_match("#^/catalog/.*$#", $_SERVER["REQUEST_URI"]))
        $arResult["IN_CATALOG"] = true;
    

    // Вычисляем ID свойства "привязка элемента каталога к товарному предложению"
    $sQuery = "SELECT `ID` FROM `b_iblock_property` WHERE "
        ." IBLOCK_ID=".OFFER_IB_ID." AND `CODE`='CML2_LINK' LIMIT 1";
    $arr = $DB->Query($sQuery)->Fetch();

    // Вычисляем ID свойства "скрывать при нулевом остатке
    $sQuery = "SELECT `ID` FROM `b_iblock_property` WHERE "
        ." IBLOCK_ID=".CATALOG_IB_ID." AND `CODE`='HIDE_IF_ABSENT' LIMIT 1";
    $arrHide = $DB->Query($sQuery)->Fetch();


    $sQuery = "
        SELECT
            -- Нам нужна только ID разделов
            `c`.`IBLOCK_SECTION_ID` as `SECTION_ID`
        FROM
            `b_iblock_element_property` as `a`
                LEFT JOIN
            `b_catalog_store_product` as `b`
                ON `a`.`IBLOCK_ELEMENT_ID`=`b`.`PRODUCT_ID`
                LEFT JOIN
            `b_iblock_element` as `c`
                ON `a`.`VALUE`=`c`.`ID`
                LEFT JOIN
            `b_iblock_element_property` as `d`
                ON ( 
                    `d`.`IBLOCK_ELEMENT_ID`=`c`.`ID`
                    AND
                    `d`.`IBLOCK_PROPERTY_ID`=".$arrHide["ID"]."
                )
                LEFT JOIN
            `b_iblock_property_enum` as `e`
                ON
                `d`.`VALUE_ENUM`=`e`.`ID` 

        WHERE
            -- Выбираем только из имеющих свойство /привязка к элементам
            -- каталога
            `a`.`IBLOCK_PROPERTY_ID`=".$arr["ID"]."
            -- элементы каталога должны бать активными
            AND `c`.`ACTIVE`='Y'
            -- Свойство СкрыватьПриНулевом остатке должно либо отсутствовать
            -- либо должны быть остатки на складах
            AND 
            (
                (
                    `b`.`AMOUNT`>0
                    AND
                    `e`.`VALUE`='да'
                )
                OR
                (
                    `d`.`ID` IS NULL
                )
            )
        GROUP BY
            `c`.`IBLOCK_SECTION_ID`
    ";
//    echo "<pre>";
//    echo $sQuery;
//    echo "</pre>";
    $res = $DB->Query($sQuery);
    $arrItems = array();
    while($arr = $res->Fetch())$arrItems[] = $arr["SECTION_ID"];
//  print_r($arrItems);

    $arParams["SECTION_ID"] = 0;
    if(preg_match("#^/catalog/(.*?)/.*$#", $_SERVER["REQUEST_URI"], $m)){
        $arSection = CIBlockSection::GetList(
            [],["CODE"=>$m[1]],false,["ID"],["nTopCount"=>1]
        )->Fetch();
        $arParams["SECTION_ID"] = $arSection["ID"];
    }


    if($arResult["IN_CATALOG"] 
        // Не выводить фильтры в карточках
        &&  count(explode("/",$_SERVER["REQUEST_URI"]))<5
    ){
        $arResult["INTERESTS"] = filterGetTags(
            INTEREST_IBLOCK_ID,INTEREST_PROPERTY_ID,
            $arParams["SECTION_ID"]
        );
    }


    CModule::IncludeModule("iblock");
    $res = CIBlockSection::GetList(
        array(),
        array(
            "ACTIVE"=>"Y",
            "IBLOCK_ID"=>CATALOG_IB_ID,
            "SECTION_ID"=>0,
            "ID"=>$arrItems
        ),
        false,
        false
    );
    $arResult["SECTIONS"] = array();
    while($section = $res->getNext()){
        $arResult["SECTIONS"][$section["ID"]] = $section;
        $res1 = CIBlockElement::GetList(
            array(),array("SECTION_ID"=>$section["ID"],"ACTIVE"=>"Y"),false
        );
        $arResult["SECTIONS"][$section["ID"]]["products"]=$res1->SelectedRowsCount();
    }
    ****** Зарефакторено **********/
 
    $this->IncludeComponentTemplate();
//}

