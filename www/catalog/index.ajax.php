<?
// Включаем безбитриксовое кеширование
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php");
// Запись в ручной кэш (в обход битрикса)
//customCache();
//customCacheClear();
//sleep(1);

define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php");
use AGShop\Integration as Integration;


    $ON_PAGE = 12;
    $PAGE = isset($_REQUEST["PAGE"])?intval($_REQUEST["PAGE"]):1;


    CModule::IncludeModule("iblock");
    CModule::IncludeModule("catalog");

    $arrFilter = array();
    $arrSorting = array("SORT"=>"ASC");
    
    // Составляем справочник флагов
    $ENUMS = array();
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>CATALOG_IB_ID));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUMS[$data["PROPERTY_CODE"]]))$ENUMS[$data["PROPERTY_CODE"]] = array();
        $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
    }

    // Составляем список разделов, из которых будем выводить
    $res = CIBlockSection::GetList(array(),array("ACTIVE"=>"Y"),false,array("ID"));
    $arSectionsIds = array();
    while($arSection = $res->Fetch())$arSectionsIds[] = $arSection["ID"];
    

    
    if(!isset($_REQUEST['sorting']) || !$_REQUEST['sorting'])$_REQUEST['sorting']='rating-desc';

    if(isset($_REQUEST['sorting']) && $_REQUEST['sorting']=='rating-desc'){
        $arrSorting["PROPERTY_RATING"]="DESC";
    }
    elseif(isset($_REQUEST['sorting']) && $_REQUEST['sorting']=='price-desc'){
        $arrSorting["PROPERTY_MINIMUM_PRICE"]="DESC";
    }
    else{
        $arrSorting["PROPERTY_MINIMUM_PRICE"]="ASC";
    }
    
    if(isset($_REQUEST['flag']) && $_REQUEST['flag']=='news'){
        $arrFilter["PROPERTY_NEWPRODUCT"] = $ENUMS['NEWPRODUCT']["да"];
    }
    if(isset($_REQUEST['flag']) && $_REQUEST['flag']=='actions'){
        $arrFilter["PROPERTY_SPECIALOFFER"] = $ENUMS['SPECIALOFFER']["да"];
    }
    if(isset($_REQUEST['flag']) && $_REQUEST['flag']=='populars'){
        $arrFilter["PROPERTY_SALELEADER"] = $ENUMS['SALELEADER']["да"];
    }
    
   
    if(isset($_REQUEST['filter_iwant']) && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_iwant'])){
        $iwant = explode(",",$_REQUEST['filter_iwant']);
        if(!count($iwant)){
        }else{
            $arrFilter["PROPERTY_WANTS"] = $iwant;
        }
    }

    if(isset($_REQUEST['filter_type']) && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_type'])){
        $type = explode(",",$_REQUEST['filter_type']);
        if(!count($type)){
        }else{
            $arrFilter["PROPERTY_TYPES"] = $type;
        }
    }
    
    if(
        isset($_REQUEST['filter_interest']) 
        && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_interest']
    )){
        $interest = explode(",",$_REQUEST['filter_interest']);
        if(!count($interest)){
        }else{
            $arrFilter["PROPERTY_INTERESTS"] = $interest;
        }
    }

    if(
        isset($_REQUEST['catalog_name']) 
        && preg_match("#^[\d\w\-]+$#i",$_REQUEST['catalog_name'])
    ){

        $resCatalogSection = CIBlockSection::GetList(
            array(),
            array("CODE"=>$_REQUEST['catalog_name']),
            false,
            array("nTopCount"=>1),array("ID")
        );
        $arCatalogSection = $resCatalogSection->GetNext();
        
        if(!isset($arCatalogSection["ID"])){
        }else{
            $arrFilter["SECTION_ID"] = $arCatalogSection["ID"];
        }
    }


    if(
        isset($_REQUEST['filter_balls']) 
        && preg_match("#^[\d\ ]+(\,[\d\ ]+)*$#",$_REQUEST['filter_balls'])
    ){
        $balls = explode(",",$_REQUEST['filter_balls']);
        $arrFilter[">=PROPERTY_MINIMUM_PRICE"] = $balls[0];
        $arrFilter["<=PROPERTY_MINIMUM_PRICE"] = $balls[1];
    }
    elseif(!isset($_REQUEST['filter_balls'])){
        $arrFilter["<=PROPERTY_MINIMUM_PRICE"] = 1000000000;
    }
    // Не выводить неактивные
    if(!preg_match("#/profile/wishes/#",$_SERVER["HTTP_REFERER"]))
        $arrFilter["ACTIVE"] = 'Y';
    // Не выводить с нулевой и отрицательной ценой
    $arrFilter[">PROPERTY_MINIMUM_PRICE"] = 0;
    
    // Узнаём ID инфоблока
    $arrFilter["IBLOCK_ID"] = CATALOG_IB_ID;
    if(!$arrFilter["SECTION_ID"]) $arrFilter["SECTION_ID"] = $arSectionsIds;

    $sUri = str_replace("http://".$_SERVER["HTTP_HOST"],"",$_SERVER["HTTP_REFERER"]);
    $sUri = str_replace("https://".$_SERVER["HTTP_HOST"],"",$sUri);
    // Запоминаем фильтры в сессии
    if(!isset($_SESSION["FILTERS"]))$_SESSION["FILTERS"] = array();
    $_SESSION["FILTERS"][$sUri] = $arrFilter;

    // Запоминаем сортировки в сессии
    if(!isset($_SESSION["SORTINGS"]))$_SESSION["SORTINGS"] = array();
    $_SESSION["SORTINGS"][$sUri] = $arrSorting;

customCache();

//    echo "<pre>";
//    print_r($arrFilter);
//    echo "</pre>";

    // Составляем справочник свойств
    $sQuery = "
        SELECT 
            `ID`,`CODE`
        FROM
            `b_iblock_property`
    ";
    $res = $DB->Query($sQuery);

    $arPropList = [];
    while($arProp = $res->Fetch())
        $arPropList[$arProp["CODE"]] = $arProp["ID"];

    $sQuerySelect = "
            `catalog`.`ID` as `ID`,
            `catalog`.`NAME` as `NAME`,
            `catalog`.`CODE` as `CODE`,
            `catalog`.`PREVIEW_TEXT` as `PREVIEW_TEXT`,
            `catalog`.`IBLOCK_SECTION_ID` as `SECTION_ID`,
            `catalog`.`DETAIL_PICTURE` as `DETAIL_PICTURE_ID`,
            `section`.`NAME` as `SECTION_NAME`,
            `section`.`CODE` as `SECTION_CODE`,
            `hide_date`.`VALUE` as `hide_date`
    ";

    $sQueryFrom = "
            `b_iblock_element` as `catalog`
                LEFT JOIN
            `b_iblock_section` as `section`
                ON
                `catalog`.`IBLOCK_SECTION_ID`=`section`.`ID`
                AND
                `section`.`IBLOCK_ID`=".CATALOG_IB_ID."
                LEFT JOIN
            `b_iblock_element_property` as `offerlink`
                ON
                `catalog`.`ID`=`offerlink`.`VALUE_NUM`
                LEFT JOIN
            `b_catalog_store_product` as `store`
                ON
                `offerlink`.`IBLOCK_ELEMENT_ID`=`store`.`PRODUCT_ID`
                AND
                `store`.`AMOUNT`>0
                LEFT JOIN
            `b_iblock_element_property` as `hide`
                ON
                `hide`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `hide`.`IBLOCK_PROPERTY_ID` = ".$arPropList["HIDE_IF_ABSENT"]."
                LEFT JOIN
            `b_iblock_element_property` as `hide_date`
                ON
                `hide_date`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `hide_date`.`IBLOCK_PROPERTY_ID` = ".$arPropList["HIDE_DATE"]."
                
    ";
    if(
        isset($arrFilter[">PROPERTY_MINIMUM_PRICE"])
        ||
        isset($arrFilter[">PROPERTY_MINIMUM_PRICE"])
        ||
        isset($arrSorting["PROPERTY_MINIMUM_PRICE"])
    )$sQueryFrom .= "
                LEFT JOIN
            `b_iblock_element_property` as `minprice`
                ON
                `minprice`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `minprice`.`IBLOCK_PROPERTY_ID`=".$arPropList["MINIMUM_PRICE"]."
    ";
    if(isset($arrFilter["PROPERTY_WANTS"]))$sQueryFrom .= "
                LEFT JOIN
            `b_iblock_element_property` as `iwant`
                ON
                `iwant`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `iwant`.`IBLOCK_PROPERTY_ID`=".$arPropList["WANTS"]."
    ";
    if(isset($arrFilter["PROPERTY_INTERESTS"]))$sQueryFrom .= "
                LEFT JOIN
            `b_iblock_element_property` as `interests`
                ON
                `interests`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `interests`.`IBLOCK_PROPERTY_ID`=".$arPropList["INTERESTS"]."
    ";
    if(isset($arrFilter["PROPERTY_SPECIALOFFER"]))$sQueryFrom .= "
                LEFT JOIN
            `b_iblock_element_property` as `specoffer`
                ON
                `specoffer`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `specoffer`.`IBLOCK_PROPERTY_ID`=".$arPropList["SPECIALOFFER"]."
    ";
    if(isset($arrFilter["PROPERTY_SALELEADER"]))$sQueryFrom .= "
                LEFT JOIN
            `b_iblock_element_property` as `salelider`
                ON
                `salelider`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `salelider`.`IBLOCK_PROPERTY_ID`=".$arPropList["SALELEADER"]."
    ";
    if(isset($arrFilter["PROPERTY_NEWPRODUCT"]))$sQueryFrom .= "
                LEFT JOIN
            `b_iblock_element_property` as `newprod`
                ON
                `newprod`.`IBLOCK_ELEMENT_ID`=`catalog`.`ID`
                AND
                `newprod`.`IBLOCK_PROPERTY_ID`=".$arPropList["NEWPRODUCT"]."
    ";
           
    
        

    $sQueryWhere = "
            1
            AND (
                `store`.`ID` IS NOT NULL
                OR
                (
                    `store`.`ID` IS NULL
                    AND
                    `hide`.`ID` IS NULL
                )
            )
            AND `catalog`.`ACTIVE`='Y'
            AND `section`.`ACTIVE`='Y'
            AND (
                `hide_date`.`VALUE`>NOW()
                OR
                `hide_date`.`VALUE` IS NULL
                OR
                `hide_date`.`VALUE`=''
            )
            AND `catalog`.`IBLOCK_ID` = ".CATALOG_IB_ID." ";
    if(isset($arrFilter["SECTION_ID"]) && intval($arrFilter["SECTION_ID"]))
        $sQueryWhere .= " 
            AND `catalog`.`IBLOCK_SECTION_ID` IN (".(
                $arrFilter["SECTION_ID"]
                ?
                (is_array($arrFilter["SECTION_ID"])?implode(",",$arrFilter["SECTION_ID"]):$arrFilter["SECTION_ID"])
                :
                0
            ).")";

    if(isset($arrFilter[">PROPERTY_MINIMUM_PRICE"]))
        $sQueryWhere .= "
            AND `minprice`.`VALUE_NUM`>".$arrFilter[">PROPERTY_MINIMUM_PRICE"].""; 
    elseif(isset($arrFilter[">=PROPERTY_MINIMUM_PRICE"]))
        $sQueryWhere .= "
            AND `minprice`.`VALUE_NUM`>=".$arrFilter[">=PROPERTY_MINIMUM_PRICE"].""; 

    if(isset($arrFilter["<=PROPERTY_MINIMUM_PRICE"]))
        $sQueryWhere .= "
            AND `minprice`.`VALUE_NUM`<=".$arrFilter["<=PROPERTY_MINIMUM_PRICE"].""; 

    if(isset($arrFilter["PROPERTY_INTERESTS"]))$sQueryWhere .= "
            AND `interests`.`VALUE_NUM` IN("
                .implode(",",$arrFilter["PROPERTY_INTERESTS"])
                .")";

    if(isset($arrFilter["PROPERTY_WANTS"]))$sQueryWhere .= "
            AND `iwant`.`VALUE_NUM` IN("
                .implode(",",$arrFilter["PROPERTY_WANTS"])
                .")";

    if(isset($arrFilter["PROPERTY_SPECIALOFFER"]))$sQueryWhere .= "
            AND `specoffer`.`ID` IS NOT NULL";

    if(isset($arrFilter["PROPERTY_SALELEADER"]))$sQueryWhere .= "
            AND `salelider`.`ID` IS NOT NULL";

    if(isset($arrFilter["PROPERTY_NEWPRODUCT"]))$sQueryWhere .= "
            AND `newprod`.`ID` IS NOT NULL";

    $sQueryLimit = "
            ".(($PAGE-1)*$ON_PAGE).",
            ".($ON_PAGE)."
      ";

    $sQuerySorting = "";
    if(isset($arrSorting["PROPERTY_RATING"])){
        $sQuerySorting = " 
        ORDER BY 
            `WISHES` "
            .$DB->ForSql($arrSorting["PROPERTY_RATING"]);
    }
    elseif(isset($arrSorting["PROPERTY_MINIMUM_PRICE"])){
        $sQuerySorting = " 
        ORDER BY 
            `minprice`.`VALUE_NUM` "
            .$DB->ForSql($arrSorting["PROPERTY_MINIMUM_PRICE"]);
    }
    if($sQuerySorting)$sQuerySorting.=",";
    $sQuerySorting .= "
            `catalog`.`SORT` ASC";


    // Запрос для определения общего числа товаров по фильтру
    $sQuery = "
        SELECT  
            COUNT(DISTINCT `catalog`.`ID`) as `COUNT`
        FROM    
            $sQueryFrom
        WHERE
            $sQueryWhere
    ";
   
    /*    
    echo "<pre>";
    echo $sQuery;
    echo "</pre>";
    echo "<pre>";
    print_r($arrFilter);
    print_r($arrSorting);
    echo "</pre>";
    */
    
   
    

    $nTotalCount = $DB->Query($sQuery)->Fetch();
    $nTotalCount = $nTotalCount["COUNT"];



    // Запрос для вывода конкретной страницы
    $sQuery = "
        SELECT
            $sQuerySelect,
            COUNT(DISTINCT `wishes`.`ID`) as `WISHES`
        FROM 
            $sQueryFrom
                LEFT JOIN
            `b_iblock_element_property` as `wishes`
                ON
                `catalog`.`ID`=`wishes`.`VALUE_NUM`
                AND
                `wishes`.`IBLOCK_PROPERTY_ID`=".$arPropList["WISH_PRODUCT"]."
        WHERE
            $sQueryWhere
        GROUP BY
            `catalog`.`ID`
        $sQuerySorting
        LIMIT 
            $sQueryLimit
    ";
    
    

    $res = $DB->Query($sQuery);
    $arProducts = [];
    $arProductsIds = [];
    // Набираем массив товаров
    while($arProduct = $res->Fetch()){
        $arProductsIds[] = $arProduct["ID"];
        if($arProduct["DETAIL_PICTURE_ID"])
           $arFilesIds[] = $arProduct["DETAIL_PICTURE_ID"];
        $arProducts[$arProduct["ID"]] = $arProduct;
    }
    // Составляем индекс свойств
    $sQuery = "
    SELECT 
        `a`.`IBLOCK_ELEMENT_ID` as `ELEMENT_ID`,
        `a`.`VALUE` as `PROPERTY_VALUE`,
        `b`.`CODE` as `PROPERTY_CODE`
    FROM
        `b_iblock_element_property` as `a`
            LEFT JOIN 
        `b_iblock_property` as `b`
            ON 
            `a`.`IBLOCK_PROPERTY_ID`=`b`.`ID`
    WHERE   
        `a`.`IBLOCK_ELEMENT_ID` IN (".(
            $arProductsIds
            ?
            implode(",",$arProductsIds)
            :
            0
            ).")
    ";
    $res = $DB->Query($sQuery);
    $arPropertyIndex = [];
    while($arProp = $res->Fetch()){
        if(!isset($arPropertyIndex[$arProp["ELEMENT_ID"]]))
            $arPropertyIndex[$arProp["ELEMENT_ID"]] = array();
        $arPropertyIndex[$arProp["ELEMENT_ID"]][$arProp["PROPERTY_CODE"]] = 
            $arProp["PROPERTY_VALUE"];
    }
    
    // Составляем индекс изображений
    $sQuery = "
        SELECT
            `ID`,
            CONCAT('/upload/iblock/',LEFT(`FILE_NAME`,3),'/',`FILE_NAME`) as `FILE_NAME`
        FROM    
            `b_file`
        WHERE 
            `ID` IN (".
                (
                    $arFilesIds
                    ?
                    implode(",",$arFilesIds)
                    :
                    0
                )
            .")
    ";
    $res = $DB->Query($sQuery);
    $arFilesIndex = [];
    while($arFile = $res->Fetch())
        $arFilesIndex[$arFile["ID"]] = $arFile["FILE_NAME"];

    // Составляем индекс флагов
    $sQuery = "
        SELECT
            `ID`,`VALUE`
        FROM 
            `b_iblock_property_enum`
    ";
    $res = $DB->Query($sQuery);
    $arEnumIndex = [];
    while($arEnum = $res->Fetch())
        $arEnumIndex[$arEnum["ID"]] = $arEnum["VALUE"];



    foreach($arProducts as $product){
        // Костылим суточные лимиты тройки-паркови
        if(preg_match("#parkov#",$product["CODE"])){
            $objParking = new \Integration\CIntegrationParking($USER->GetLogin());
            $objParking->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bIsLimited = $objParking->isLimited();
            if($bIsLimited)continue;
        }
        if(preg_match("#troyka#",$product["CODE"])){
            $objTroya = new \Integration\CIntegrationTroyka($USER->GetLogin());
            $objTroya->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bIsLimited = $objTroya->isLimited();
            if($bIsLimited)continue;
        }
        $product["DETAIL_PAGE_URL"] = "/catalog/"
            .$product["SECTION_CODE"]."/"
            .$product["CODE"]."/";

        $product["PROPERTY_MINIMUM_PRICE_VALUE"] = $arPropertyIndex[$product["ID"]]["MINIMUM_PRICE"];
        $product['PREVIEW_TEXT'] = strip_tags($product['PREVIEW_TEXT']);

        // Выбираем цвет настроения
        /*
            ЗЕЛЕНЫЙ
            На природу  
            Кататься

            СЕРЫЙ
            Подарок детям
            Что-то на память

            РОЗОВЫЙ
            Романтики
            Развлечься

            ГОЛУБОЙ
            Отдохнуть
            Развиваться
        */
        $sWant = $product["ALL_PROPERTIES"]["WANTS"]["VALUE_ENUM"];
        $sClassName = "feel_so_green";
        if($sWant=='Подарок детям' || $sWant=='Что-то на память')
            $sClassName = 'feel_so_yellow';
        elseif($sWant=='Романтики' || $sWant=='Развлечься')
            $sClassName = 'feel_so_pink';
        elseif($sWant=='Отдохнуть' || $sWant=='Развиваться')
            $sClassName = 'feel_so_blue';
        ?>
        
                <div class="grid__col-shrink">
                  <div class="ag-shop-catalog__item">
                    <!-- Обычная карточка товара-->
                    <button class="ag-shop-slider-card__likes" type="button">
                      <div class="ag-shop-slider-card__likes-icon<?
                      if($arWishes[$product["ID"]]["MY"]){?> wish-on<? }else{?> wish-off<? }?>"
                        productid="<?= $product["ID"]?>" 
                        <? if($USER->IsAuthorized() && !$arResult["MARK"]):?>
                        onclick="return mywish(this);"
                        <? endif ?>
                      ></div>
                      <div class="ag-shop-slider-card__likes-count"
                      id="wishid<?= $product["ID"]?>"><?= 
                        $product["WISHES"]
                      ?></div>
                    </button>
                      <?
                        // Костыль для ссылок товаров без каталога
                        $arSplitUrl = explode("/",$product["DETAIL_PAGE_URL"]);
                        if(count($arSplitUrl)==4 && $arSplitUrl[1]=='catalog')
                           $product["DETAIL_PAGE_URL"] = "/catalog/root/"
                           .$arSplitUrl[2]."/";
                     ?>
                      <a class="ag-shop-item-card <?= IS_MOBILE ? 'ag-shop-item-card--app' : '' ?>" href="<?= $product["DETAIL_PAGE_URL"]?>" title="<?= $product["NAME"];?>"
                      style="background-image: url(<?=
                      $arFilesIndex[$product["DETAIL_PICTURE_ID"]]?>);">
                        <div class="ag-shop-item-card-cover <?= $sClassName?>"></div>
                        <div class="ag-shop-item-card__points">
                          <div class="ag-shop-item-card__points-count"><?= number_format($product["PROPERTY_MINIMUM_PRICE_VALUE"],0,","," ")?></div>
                          <div class="ag-shop-item-card__points-text"><?= get_points($product["PROPERTY_MINIMUM_PRICE_VALUE"])?></div>
                        </div>
                      <div class="ag-shop-item-card__badges">
                      <?
                      if($arEnumIndex[$arPropertyIndex[$product["ID"]]["NEWPRODUCT"]]=='да'):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__new.png">
                      <? endif?>
                      <?
                      if($arEnumIndex[$arPropertyIndex[$product["ID"]]["SALELEADER"]]=='да'):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__hit.png">
                      <? endif?>
                      <?
                      if($arEnumIndex[$arPropertyIndex[$product["ID"]]["SPECIALOFFER"]]=='да'):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__sale.png">
                      <? endif?>
                      </div>
                          <h3 class="ag-shop-item-card__name"><?= $product["NAME"];?></h3>
                      <div class="ag-shop-item-card__info-layer">
                        <div class="ag-shop-item-card__colors">
                        </div>
                        <div class="ag-shop-item-card__info">
                          <h3 class="ag-shop-item-card__name"><?= $product["NAME"];?></h3>
                          <p class="ag-shop-item-card__category"><?=
                          $product["SECTION_NAME"];?></p>
                          <div class="ag-shop-item-card__rating">
                            <? /*  Пока убираем рейтинг */?>
                            <? if(0)for($i=0;$i<round($product["RATING"]);$i++):?>
                            <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                            <? endfor ?>
                            <? if(0)for($j=0;$j<5-round($product["RATING"]);$j++):?>
                            <div class="ag-shop-slider-card__rating-item"></div>
                            <? endfor ?>
                          </div>
                          <div class="ag-shop-item-card__sizes">
                            <!--
                            Размеры:
                            <span class="ag-shop-item-card__sizes-content">M &nbsp;|&nbsp; L &nbsp;|&nbsp; XL</span>
                            -->
                          </div>
                          <p class="ag-shop-item-card__description">
                              <?= mb_strlen($product["PREVIEW_TEXT"])<196?$product["PREVIEW_TEXT"]:mb_substr($product["PREVIEW_TEXT"],0,196)."..."?>
                          </p>
                        </div>
                      </div>
                      </a>
                  </div>
                </div>
        
        <?
    }
    
    $request = "";
    foreach($_REQUEST as $key=>$value){$request.="$key=$value&";}
    
    ?>
  

    <? if(!$nTotalCount):?>
    <div class="grid__col-shrink">
        <div class="ag-shop-catalog__item">
            <h2 style="text-align:center;color: rgba(0,122,108,1)">Нет товаров,
            <br/>удовлетворяющих <br/>условиям
            фильтра</h2>
        </div>
    </div>
    <? endif ?>


    <?if(
        $nTotalCount>($PAGE*$ON_PAGE)
    ):?>
        <input type="hidden" name="products" value="<?=
        implode(",",$arProductsIds)?>">
        <input type="hidden" class="catalog-page-input" value="<?= $request."PAGE=".($PAGE+1);?>"/>
    <?else:?>
    <?endif?>
    
    <?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
