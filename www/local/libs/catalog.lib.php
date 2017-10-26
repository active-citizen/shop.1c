<?
    /**
        Функция для получения списка тегов
    */
    function filterGetTags(
        $nIblockId, //!< ID инфоблока с тегами
        $nWantPropertyId, //!< ID св-ва в инфблоке товаров, ссылающееся на тег
        $nSectionId = 0 //!< ID раздела
    ){
        global $DB;

        // Если кэш не протух - даём из кэша
        $arResult = filterGetCache(
            $nIblockId,
            $nWantPropertyId,
            $nSectionId,
            $nLifetime = 600
        );
        if($arResult!==false){
            return $arResult;
        }

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

        // Сохраняем результат запроса в кэш
        filterSaveCache(
            $result,
            $nIblockId,
            $nWantPropertyId,
            $nSectionId
        );
        return $result;
    }

    function filterSaveCache(
        $arResult,
        $nIblockId,
        $nWantPropertyId,
        $nSectionId
    ){
        $sCacheFilename = filterGetCacheFilename(
            $nIblockId,
            $nWantPropertyId,
            $nSectionId
        );

        $fd = fopen($sCacheFilename, "w");
        fwrite($fd, serialize($arResult));
        fclose($fd);
    }

    function filterGetCache(
        $nIblockId,
        $nWantPropertyId,
        $nSectionId,
        $nLifetime = 600
    ){
        $sCacheFilename = filterGetCacheFilename(
            $nIblockId,
            $nWantPropertyId,
            $nSectionId
        );

        $mtime = 0;
        if(file_exists($sCacheFilename)){
            $stat = stat($sCacheFilename);
            $mtime = $stat['mtime'];
            if(($mtime+$nLifetime)>time())
                return unserialize(file_get_contents($sCacheFilename));
        }
        return false;
    }

    function filterGetCacheFilename(
        $nIblockId,
        $nWantPropertyId,
        $nSectionId
    ){
        if(!$nSection)$nSection=0;
         return $_SERVER["DOCUMENT_ROOT"]
            ."/upload/filter_cache/$nIblockId.$nWantPropertyId.$nSectionId.cache";
    }
