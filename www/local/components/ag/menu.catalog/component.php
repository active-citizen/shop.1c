<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


//if ($this->StartResultCache(false)) {
    // Получаем корневых разделов

    $sQuery = "SELECT `ID` FROM `b_iblock_property` WHERE "
        ." IBLOCK_ID=".OFFER_IB_ID." AND `CODE`='CML2_LINK' LIMIT 1";
    $arr = $DB->Query($sQuery)->Fetch();

    $sQuery = "SELECT `ID` FROM `b_iblock_property` WHERE "
        ." IBLOCK_ID=".CATALOG_IB_ID." AND `CODE`='HIDE_IF_ABSENT' LIMIT 1";
    $arrHide = $DB->Query($sQuery)->Fetch();



    $sQuery = "
        SELECT
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
            `a`.`IBLOCK_PROPERTY_ID`=".$arr["ID"]."
            AND `c`.`ACTIVE`='Y'
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
 
    $this->IncludeComponentTemplate();
//}

