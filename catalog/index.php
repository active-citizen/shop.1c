<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

    // Получаем список банеров
    CModule::IncludeModule("iblock");
    $res = CIBlockElement::GetList(array(),array("ACTIVE"=>"Y","IBLOCK_CODE"=>"baners_on_main",),false,false);
    $BANERS = array();
    while($baner = $res->getNext()){
        $BANERS[$baner["ID"]] = $baner;
        $BANERS[$baner["ID"]]["PROPERTIES"] = array();
        $res1 = CIBlockElement::GetProperty($baner["IBLOCK_ID"],$baner["ID"]);
        while($prop = $res1->getNext()){
            if($prop["PROPERTY_TYPE"]=='F')$prop["URL"] = CFile::GetPath($prop["VALUE"]);
            $BANERS[$baner["ID"]]["PROPERTIES"][$prop["CODE"]] = $prop;
        }
    }
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"WANTS"));
    $IWANTS = array();
    while($iwant = $res->getNext())$IWANTS[$iwant["ID"]]=$iwant;
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"INTERESTS"));
    $INTERESTS = array();
    while($interest = $res->getNext())$INTERESTS[$interest["ID"]]=$interest;
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"TYPES"));
    $TYPES = array();
    while($type = $res->getNext())$TYPES[$type["ID"]]=$type;
?>


            <div class="ag-baners">
                <div class="fotorama" 
                    data-loop="true" 
                    data-autoplay="true" 
                    data-fit="cover"
                    data-width="100%"
                    data-height="300px"
                    data-nav="dots"
                    data-navPosition="bottom"
                    data-dotColor="red"
                    data-transition="slide"
                    data-transitionduration="500"
                    data-arrows = "false"
                >
                <?foreach($BANERS as $baner):?>
                    <div data-img="<?= $baner["PROPERTIES"]["BANER_PICTURE"]["URL"]?>">
                        <div class="ag-baner-name"><?= $baner["NAME"]?></div>
                        <div class="ag-baner-desc"><?= $baner["PROPERTIES"]["BANER_DESC"]["VALUE"]?></div>
                        <a href="<?= $baner["PROPERTIES"]["BANER_URL"]["VALUE"]?>">
                        </a>
                    </div>
                <?endforeach?>
                </div>
            </div>

            <a name="products"><h1></h1></a>
            <div class="ad-main-filter">
                <div class="title">Что выбрать?</div>
                <div class="slogan">Подбери поощрение своей мечты, попробуй новый сервис &laquo;Что выбрать?&raquo;</div>
                <div class="ag-filter-params">
                    <!-- 
                    <select name="type" id="ag-type" class="ag-filter-param">
                        <option value="0">--Тип--</option>
                        <?foreach($TYPES as $type):?>
                        <option value="<?= $type["ID"]?>"<?if(isset($_REQUEST["filter_type"]) && $_REQUEST["filter_type"]==$type["ID"]):?> selected<?endif?>><?= $type["VALUE"]?></option>    
                        <?endforeach?>
                    </select>
                    -->
                    <div class="ag-filter-param">
                        <select name="iwant" id="ag-iwant">
                            <option value="0">-Хочу-</option>
                            <?foreach($IWANTS as $iwant):?>
                            <option value="<?= $iwant["ID"]?>"<?if(isset($_REQUEST["filter_iwant"]) && $_REQUEST["filter_iwant"]==$iwant["ID"]):?> selected<?endif?>><?= $iwant["VALUE"]?></option>    
                            <?endforeach?>
                        </select>
                    </div>
                    <div class="ag-filter-param"></div>
                    <div class="ag-filter-param">
                        <select name="interest" id="ag-interest">
                            <option value="0">-Интересуюсь-</option>
                            <?foreach($INTERESTS as $interest):?>
                            <option value="<?= $interest["ID"]?>"<?if(isset($_REQUEST["filter_interest"]) && $_REQUEST["filter_interest"]==$interest["ID"]):?> selected<?endif?>><?= $interest["VALUE"]?></option>    
                            <?endforeach?>
                        </select>
                    </div>
                    <div class="ag-filter-param"></div>
                    <!-- <div class="ag-filter-param"><div class="ag-label">В пределах</div></div> -->
                    <div class="ag-filter-param"></div>
                    <div class="ag-filter-param" id="ag-balls-cont">
                        <input type="text" name="balls" id="ag-balls" value="<?= (intval($_REQUEST["filter_balls"])?$_REQUEST["filter_balls"]:1500) ?>">
                        баллов
                    </div>
                    <div class="ag-filter-param"></div>
                    <!-- <div id="ag-show" class="ag-filter-param">Попробовать</div> -->
                </div>
                <div class="ag-filter-params">
                    <input type="hidden" id="ag-types" name="ag-types" value="">
                    <?foreach($TYPES as $type):?>
                    <label 
                        style="background-image:url(/bitrix/templates/agnew/i/activities/<?= md5($type["VALUE"])?>.png);"
                        <?if(isset($_REQUEST["filter_type"]) && $_REQUEST["filter_type"]==$type["ID"]):?> class="radio-active"<?endif?>
                        rel="<?= $type["ID"]?>"
                    >
                        <?= $type["VALUE"];?>
                    </label>
                    <?endforeach?>
                </div>
                <input type="hidden" id="ag-flag" name="ag-flag" value="<?= !isset($_REQUEST["flag"])?"all":htmlspecialchars(($_REQUEST["flag"]))?>">
            </div>
    
            <?
                $GETARRAY = $_REQUEST;
                if(isset($GETARRAY["flag"]))unset($GETARRAY["flag"]);
                if(isset($GETARRAY["PAGEN_1"]))unset($GETARRAY["PAGEN_1"]);
                $BASEURL = array();
                foreach($GETARRAY as $key=>$value)$BASEURL[] = "$key=$value";
                $BASEURL="?".implode("&",$BASEURL);
                if(!isset($_REQUEST["flag"]) || !trim($_REQUEST["flag"]))$_REQUEST["flag"] = 'all';
            ?>
            <div class="ag-section-title">
                <a href="#" rel="all" class="filter-flag">Все товары</a>
                |
                <a href="#" rel="actions" class="filter-flag">Акции</a>
                |
                <a href="#" rel="news" class="filter-flag">Новые поступления</a>
                |
                <a href="#" rel="populars" class="filter-flag">Популярные</a>
            </div>

<?
    global $arrFilter;
    $arrFilter = array();
    
    // Составляем справочник флагов
    $ENUMS = array();
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>2));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUMS[$data["PROPERTY_CODE"]]))$ENUMS[$data["PROPERTY_CODE"]] = array();
        $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
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

    if(isset($_REQUEST['filter_iwant']) && $iwant = intval($_REQUEST['filter_iwant'])){
        $arrFilter["PROPERTY_WANTS"] = $iwant;
    }

    if(isset($_REQUEST['filter_type']) && $type = intval($_REQUEST['filter_type'])){
        $arrFilter["PROPERTY_TYPES"] = $type;
    }
    
    if(isset($_REQUEST['filter_interest']) && $interest = intval($_REQUEST['filter_interest'])){
        $arrFilter["PROPERTY_INTERESTS"] = $interest;
    }

    if(isset($_REQUEST['filter_balls']) && $balls = intval($_REQUEST['filter_balls'])){
        $arrFilter["<=CATALOG_PRICE_1"] = $balls;
    }
    
    
//    echo "<pre>";
//    print_r($ENUMS);
//    echo "</pre>";
    
    /*
    $APPLICATION->IncludeComponent(
    "bitrix:catalog.section",
    ".default",
    array(
        "USE_FILTER"=>"Y",
        "IBLOCK_TYPE_ID" => "catalog",
        "IBLOCK_ID" => "2",
        "BASKET_URL" => "/personal/cart/",
        "COMPONENT_TEMPLATE" => "",
        "IBLOCK_TYPE" => "catalog",
        "SECTION_ID" => 0,
        "SECTION_CODE" => "",
        "SECTION_USER_FIELDS" => array(
            0 => "",
            1 => "",
        ),
        "ELEMENT_SORT_FIELD" => "sort",
        "ELEMENT_SORT_ORDER" => "desc",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER2" => "desc",
        "FILTER_NAME" => "arrFilter",
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "HIDE_NOT_AVAILABLE" => "N",
        "PAGE_ELEMENT_COUNT" => "12",
        "LINE_ELEMENT_COUNT" => "3",
        "PROPERTY_CODE" => array(
            0 => "",
            1 => "",
        ),
        "OFFERS_FIELD_CODE" => array(
            0 => "",
            1 => "",
        ),
        "OFFERS_PROPERTY_CODE" => array(
            0 => "COLOR_REF",
            1 => "SIZES_SHOES",
            2 => "SIZES_CLOTHES",
            3 => "",
        ),
        "OFFERS_SORT_FIELD" => "sort",
        "OFFERS_SORT_ORDER" => "desc",
        "OFFERS_SORT_FIELD2" => "id",
        "OFFERS_SORT_ORDER2" => "desc",
        "OFFERS_LIMIT" => "5",
        "TEMPLATE_THEME" => "site",
        "PRODUCT_DISPLAY_MODE" => "Y",
        "ADD_PICT_PROP" => "MORE_PHOTO",
        "LABEL_PROP" => "-",
        "OFFER_ADD_PICT_PROP" => "-",
        "OFFER_TREE_PROPS" => array(
            0 => "COLOR_REF",
            1 => "SIZES_SHOES",
            2 => "SIZES_CLOTHES",
        ),
        "PRODUCT_SUBSCRIPTION" => "N",
        "SHOW_DISCOUNT_PERCENT" => "N",
        "SHOW_OLD_PRICE" => "Y",
        "SHOW_CLOSE_POPUP" => "N",
        "MESS_BTN_BUY" => "Купить",
        "MESS_BTN_ADD_TO_BASKET" => "В корзину",
        "MESS_BTN_SUBSCRIBE" => "Подписаться",
        "MESS_BTN_DETAIL" => "Подробнее",
        "MESS_NOT_AVAILABLE" => "Нет в наличии",
        "SECTION_URL" => "",
        "DETAIL_URL" => "",
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "SEF_MODE" => "N",
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "Y",
        "AJAX_OPTION_ADDITIONAL" => "",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_GROUPS" => "Y",
        "SET_TITLE" => "Y",
        "SET_BROWSER_TITLE" => "Y",
        "BROWSER_TITLE" => "-",
        "SET_META_KEYWORDS" => "Y",
        "META_KEYWORDS" => "-",
        "SET_META_DESCRIPTION" => "Y",
        "META_DESCRIPTION" => "-",
        "SET_LAST_MODIFIED" => "N",
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "ADD_SECTIONS_CHAIN" => "N",
        "CACHE_FILTER" => "N",
        "ACTION_VARIABLE" => "action",
        "PRODUCT_ID_VARIABLE" => "id",
        "PRICE_CODE" => array(
            0 => "BASE",
        ),
        "USE_PRICE_COUNT" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "PRICE_VAT_INCLUDE" => "Y",
        "CONVERT_CURRENCY" => "N",
        "USE_PRODUCT_QUANTITY" => "N",
        "PRODUCT_QUANTITY_VARIABLE" => "",
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "PRODUCT_PROPS_VARIABLE" => "prop",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "PRODUCT_PROPERTIES" => array(
        ),
        "OFFERS_CART_PROPERTIES" => array(
            0 => "COLOR_REF",
            1 => "SIZES_SHOES",
            2 => "SIZES_CLOTHES",
        ),
        "ADD_TO_BASKET_ACTION" => "ADD",
        "PAGER_TEMPLATE" => "round",
        "DISPLAY_TOP_PAGER" => "Y",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_TITLE" => "Товары",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_BASE_LINK_ENABLE" => "N",
        "SET_STATUS_404" => "N",
        "SHOW_404" => "N",
        "MESSAGE_404" => "",
        "USE_SALE_BESTSELLERS"=>"Y"
    ),
    false
);
*/

?>

<div class="catalog-ajax-block catalog-ajax-block-loader"></div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>