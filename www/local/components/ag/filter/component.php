<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->StartResultCache(false, CUser::GetID())) {
    CModule::IncludeModule("sale");
    $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
    $arResult['account'] = $res->GetNext();
    

    /*
    $arResult['MY_BALLS'] = number_format(
        $arResult['account']["CURRENT_BUDGET"],0 ,',',' '
    );    
    $arResult['myBalls'] = 
        $arResult['MY_BALLS'] 
        ." ".get_points($arResult['account']["CURRENT_BUDGET"]);


    ////////////////////////////////////////////////////
    /////  Составляем справочник хотелок
    ////////////////////////////////////////////////////
    $arResult["IWANTS"] = filterGetTags(
        IWANT_IBLOCK_ID,IWANT_PROPERTY_ID,
        $arParams["SECTION_ID"]
    );
    */


    ////////////////////////////////////////////////////
    /////  Составляем справочник интересов
    ////////////////////////////////////////////////////
    $arResult["INTERESTS"] = filterGetTags(
        INTEREST_IBLOCK_ID,INTEREST_PROPERTY_ID,
        $arParams["SECTION_ID"]
    );




    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"TYPES"));
    $TYPES = array();
    while($type = $res->getNext())$TYPES[$type["ID"]]=$type;

    $this->IncludeComponentTemplate();
}


    /**
        Функция для получения списка тегов
    */
    function filterGetTags(
        $nIblockId, //!< ID инфоблока с тегами
        $nWantPropertyId, //!< ID св-ва в инфблоке товаров, ссылающееся на тег
        $nSectionId = 0 //!< ID раздела
    ){
        global $DB;
        $nSectionId = intval($nSectionId);
        $nCmlPropertyId = CML2_LINK_PROPERTY_ID;
        $nHideIfAbsentPropertyId = HIDE_IF_ABSENT_PROPERTY_ID; 
        $nYesEnum = YES_HIDE_FLAG_ID;
        $sQuery = "
            SELECT
                -- Имя тега
                `a`.`NAME` as `NAME`
                -- ID тега
                ,`a`.`ID` as `ID`
                -- Число элементов у тега
                -- , COUNT(DISTINCT `g`.`ID`) as `COUNT`
            FROM
                -- Таблица тегов
                `b_iblock_element` as `a`
                    LEFT JOIN
                `b_iblock_element_property` as `b`
                    ON 
                        `b`.`IBLOCK_PROPERTY_ID`=".$nWantPropertyId."
                        AND
                        `a`.`ID`=`b`.`VALUE`
                   LEFT JOIN
                `b_iblock_element_property` as `d`
                    ON
                        `d`.`IBLOCK_PROPERTY_ID`=".$nCmlPropertyId."
                        AND
                        `b`.`IBLOCK_ELEMENT_ID`=`d`.`VALUE`
                    LEFT JOIN
                `b_catalog_store_product` as `e`
                    ON
                        `e`.`PRODUCT_ID`=`d`.`IBLOCK_ELEMENT_ID`
                    LEFT JOIN
                `b_iblock_element_property` as `f`
                    ON 
                        `f`.`IBLOCK_PROPERTY_ID`=".$nHideIfAbsentPropertyId."
                        AND
                        `b`.`IBLOCK_ELEMENT_ID`=`f`.`IBLOCK_ELEMENT_ID`
                    LEFT JOIN
                `b_iblock_element` as `g`
                    ON 
                        `b`.`IBLOCK_ELEMENT_ID`=`g`.`ID`
            WHERE
                `a`.`IBLOCK_ID` = ".$nIblockId."
                AND `a`.`ACTIVE`='Y'
                AND `g`.`ACTIVE`='Y'"
                .(
                    $nSectionId
                    ?
                    "AND `g`.`IBLOCK_SECTION_ID`=".$nSectionId
                    :
                    ""

                )."                
                AND (
                    `f`.`VALUE_ENUM`!=".$nYesEnum."
                    ||
                    `f`.`VALUE_ENUM` IS NULL
                    ||
                    (
                        `f`.`VALUE_ENUM`=".$nYesEnum."
                        AND 
                        `e`.`AMOUNT`>0
                    )
                )
            GROUP BY
                `a`.`ID`
        ";

        $resQuery = $DB->Query($sQuery);
        $result = array();
        while($arQuery = $resQuery->Fetch()){
            $result[$arQuery["ID"]] = $arQuery;
        }
        return $result;
    }

