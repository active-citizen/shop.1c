<?
// Включаем безбитриксовое кеширование
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php");
// Запись в ручной кэш (в обход битрикса)
customCache();
//customCacheClear();


define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $ON_PAGE = 10;
    $PAGE = isset($_REQUEST["PAGE"])?intval($_REQUEST["PAGE"]):1;


    CModule::IncludeModule("iblock");
    CModule::IncludeModule("catalog");

    $arrFilter = array();
    $arrSorting = array();
    
    // Составляем справочник флагов
    $ENUMS = array();
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>CATALOG_IB_ID));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUMS[$data["PROPERTY_CODE"]]))$ENUMS[$data["PROPERTY_CODE"]] = array();
        $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
    }

    
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
    
    if(isset($_REQUEST['filter_interest']) && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_interest'])){
        $interest = explode(",",$_REQUEST['filter_interest']);
        if(!count($interest)){
        }else{
            $arrFilter["PROPERTY_INTERESTS"] = $interest;
        }
    }

    if(isset($_REQUEST['catalog_name']) && preg_match("#^[\d\w\-]+$#i",$_REQUEST['catalog_name'])){

        $resCatalogSection = CIBlockSection::GetList(array(),array("CODE"=>$_REQUEST['catalog_name']),false,array("nTopCount"=>1),array("ID"));
        $arCatalogSection = $resCatalogSection->GetNext();
        
        if(!isset($arCatalogSection["ID"])){
        }else{
            $arrFilter["IBLOCK_SECTION_ID"] = $arCatalogSection["ID"];
            $arrFilter["SECTION_ID"] = $arCatalogSection["ID"];
        }
    }


    if(isset($_REQUEST['filter_balls']) && preg_match("#^[\d\ ]+(\,[\d\ ]+)*$#",$_REQUEST['filter_balls'])){
        $balls = explode(",",$_REQUEST['filter_balls']);
        $arrFilter[">=PROPERTY_MINIMUM_PRICE"] = $balls[0];
        $arrFilter["<=PROPERTY_MINIMUM_PRICE"] = $balls[1];
        /*
        // Узнаём ID инфоблока
        $res = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"));
        $iblock = $res->GetNext();
        //$arrFilter["<=CATALOG_PRICE_1"] = $balls;
        $arrFilter[] = array(
            "LOGIC"=>"OR",
            array(
                "<=CATALOG_PRICE_1"=>$balls
            ),
            array(
                "=ID"=>CIBlockElement::SubQuery("PROPERTY_CML2_LINK",array("IBLOCK_ID"=>$iblock["ID"],"<=CATALOG_PRICE_1"=>$balls))
            )
        );
        */
    }
    elseif(!isset($_REQUEST['filter_balls'])){
        $arrFilter["<=PROPERTY_MINIMUM_PRICE"] = 1000000000;
    }
    // Не выводить неактивные
    $arrFilter["ACTIVE"] = 'Y';
    // Не выводить с нулевой и отрицательной ценой
    $arrFilter[">PROPERTY_MINIMUM_PRICE"] = 0;
    
    // Узнаём ID инфоблока
    $arrFilter["IBLOCK_ID"] = CATALOG_IB_ID;
    
    $res = CIBlockElement::GetList(
        $arrSorting,
        $arrFilter,
        false,
        array("iNumPage"=>$PAGE,"nPageSize"=>$ON_PAGE),
        array(
            "PROPERTY_RATING","PROPERTY_MINIMUM_PRICE","ID","DETAIL_PICTURE",
            "DETAIL_PAGE_URL","PREVIEW_TEXT","IBLOCK_SECTION_ID","NAME"
            )
    );
    
    while($product = $res->GetNext()){
        // Получение всех свойств товара
        $res2 = CIBlockElement::GetProperty($arrFilter["IBLOCK_ID"],$product["ID"]);
        $product["ALL_PROPERTIES"] = array();
        while($row = $res2->GetNext())$product["ALL_PROPERTIES"][$row["CODE"]] = $row;
        
        $image_url = '';
        if($file_id = intval($product["DETAIL_PICTURE"]))$image_url = CFile::GetPath($file_id);

        // Входит ли товар с писок моих желаний
        $arFilter = array("IBLOCK_CODE"=>"whishes", "PROPERTY_WISH_USER"=>CUser::GetID(),"PROPERTY_WISH_PRODUCT"=>$product["ID"]);
        $res1 = CIBlockElement::GetList(array(),$arFilter,false, array("nTopCount"=>1));
        $product["mywish"] = $res1->SelectedRowsCount();
        
        // Сколько у товара всего желающих
        $arFilter = array("IBLOCK_CODE"=>"whishes", "PROPERTY_WISH_PRODUCT"=>$product["ID"]);
        $res1 = CIBlockElement::GetList(array(),$arFilter,false, array());
        $product["wishes"] = $res1->SelectedRowsCount();

        // Вычисляем раздел
        $resCatalogSection = CIBlockSection::GetList(
            array(),
            array(
                "IBLOCK_ID"   =>  CATALOG_IB_ID,
                "ID"=>$product["IBLOCK_SECTION_ID"]
            ),
            false,
            array("nTopCount"=>1),
            array("NAME")
        );
        $arCatalogSection = $resCatalogSection->GetNext();
        $product["SECTION_NAME"] = $arCatalogSection["NAME"];

        // Вычисляем рейтинг
        $product["RATING"] = round($product["PROPERTY_RATING_VALUE"]*5,2);
        // Обеззараживаем текст описания
        $product["PREVIEW_TEXT"] = strip_tags($product["PREVIEW_TEXT"]);


        $product["mark"] = $product["PROPERTY_RATING_VALUE"];
        ?>
        
                <div class="grid__col-shrink">
                  <div class="ag-shop-catalog__item">
                    <!-- Обычная карточка товара-->
                    <button class="ag-shop-slider-card__likes" type="button">
                      <div class="ag-shop-slider-card__likes-icon<? if($product["mywish"]){?> wish-on<? }else{?> wish-off<? }?>"
                        productid="<?= $product["ID"]?>" 
                        <? if($USER->IsAuthorized() && !$arResult["MARK"]):?>
                        onclick="return mywish(this);"
                        <? endif ?>
                      ></div>
                      <div class="ag-shop-slider-card__likes-count"
                      id="wishid<?= $product["ID"]?>"><?= 
                        $product["wishes"]
                      ?></div>
                    </button>
                      <?
                        // Костыль для ссылок товаров без каталога
                        $arSplitUrl = explode("/",$product["DETAIL_PAGE_URL"]);
                        if(count($arSplitUrl)==4 && $arSplitUrl[1]=='catalog')
                           $product["DETAIL_PAGE_URL"] = "/catalog/root/"
                           .$arSplitUrl[2]."/";
                     ?>
                      <a class="ag-shop-item-card" href="<?= $product["DETAIL_PAGE_URL"]?>" title="<?= $product["NAME"];?>"
                      style="background-image: url(<?= $image_url?>);">
                      <div class="ag-shop-item-card__badges">
                      <? if($product["ALL_PROPERTIES"]["NEWPRODUCT"]["VALUE_ENUM"]=='да'):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__new.png">
                      <? endif?>
                      <? if($product["ALL_PROPERTIES"]["SALELEADER"]["VALUE_ENUM"]=='да'):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__hit.png">
                      <? endif?>
                      <? if($product["ALL_PROPERTIES"]["SPECIALOFFER"]["VALUE_ENUM"]=='да'):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__sale.png">
                      <? endif?>
                      </div>
                      <div class="ag-shop-item-card__info-layer">
                        <div class="ag-shop-item-card__points">
                          <div class="ag-shop-item-card__points-count"><?= number_format($product["PROPERTY_MINIMUM_PRICE_VALUE"],0,","," ")?></div>
                          <div class="ag-shop-item-card__points-text"><?= get_points($product["PROPERTY_MINIMUM_PRICE_VALUE"])?></div>
                        </div>
                        <div class="ag-shop-item-card__colors">
                          <!--
                          <div class="ag-shop-item-card__colors-item" style="background-color:#ffffff"></div>
                          <div class="ag-shop-item-card__colors-item" style="background-color:#80807E"></div>
                          <div class="ag-shop-item-card__colors-item" style="background-color:#0F0F0F"></div>
                          -->
                        </div>
                        <div class="ag-shop-item-card__info">
                          <h3 class="ag-shop-item-card__name"><?= $product["NAME"];?></h3>
                          <p class="ag-shop-item-card__category"><?= $product["SECTION_NAME"];?></p>
                          <div class="ag-shop-item-card__rating">
                            <? for($i=0;$i<round($product["RATING"]);$i++):?>
                            <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                            <? endfor ?>
                            <? for($j=0;$j<5-round($product["RATING"]);$j++):?>
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
    
    <?if($res->SelectedRowsCount()>($PAGE*$ON_PAGE)):?>
        <input type="hidden" class="catalog-page-input" value="<?= $request."PAGE=".($PAGE+1);?>"/>
    <?else:?>
    <?endif?>
    
    <?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
