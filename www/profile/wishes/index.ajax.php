<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/rus.lib.php");

    $ON_PAGE = 10;
    $PAGE = isset($_REQUEST["PAGE"])?intval($_REQUEST["PAGE"]):1;
    if(!$PAGE)$PAGE = 1;


    CModule::IncludeModule("iblock");
    CModule::IncludeModule("catalog");

    $arrFilter = array();
    $arrSorting = array();
    
    // Составляем справочник флагов
    $ENUMS = array();
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>2));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUMS[$data["PROPERTY_CODE"]]))$ENUMS[$data["PROPERTY_CODE"]] = array();
        $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
    }

    
    // Узнаём ID инфоблока пожеланий
    $res = CIBlock::GetList(array(),array("CODE"=>"whishes"));
    $iblock = $res->GetNext();
    $arrFilter["IBLOCK_ID"] = $iblock["ID"];
    $arrFilter["PROPERTY_WISH_USER"] = $USER->GetId();
    
    // Узнаём ID инфоблока товаров
    $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
    $iblock = $res->GetNext();
    $catalogIbId = $iblock["ID"];
    
    $res = CIBlockElement::GetList(
        array("TIMESTAMP_X"=>"DESC"),
        $arrFilter,
        false,
        array("iNumPage"=>$PAGE,"nPageSize"=>$ON_PAGE),
        array("PROPERTY_WISH_PRODUCT")
    );

    if($res->result->num_rows):
    
    while($wishProduct = $res->GetNext()){
        
        // Получаем продукт, который привязан
        $resWishCatalog = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID"=>$catalogIbId,"ID"=>$wishProduct["PROPERTY_WISH_PRODUCT_VALUE"]),
            false,
            array("nPageSize"=>1),
            array(
                "PROPERTY_RATING","PROPERTY_MINIMUM_PRICE","ID","DETAIL_PICTURE",
                "DETAIL_PAGE_URL","PREVIEW_TEXT","IBLOCK_SECTION_ID","NAME"
                )
        );
        $product = $resWishCatalog->GetNext();
        if(!$product)continue;
        
        // Получение всех свойств товара
        $res2 = CIBlockElement::GetProperty($catalogIbId,$product["ID"]);
        $product["ALL_PROPERTIES"] = array();
        while($row = $res2->GetNext())$product["ALL_PROPERTIES"][$row["CODE"]] = $row;
        
        $image_url = '';
        if($file_id = intval($product["DETAIL_PICTURE"]))$image_url = CFile::GetPath($file_id);

        $product["mywish"] = 1;
        
        // Сколько у товара всего желающих
        $arFilter = array("IBLOCK_CODE"=>"whishes", "PROPERTY_WISH_PRODUCT"=>$product["ID"]);
        $res1 = CIBlockElement::GetList(array(),$arFilter,false, array());
        $product["wishes"] = $res1->SelectedRowsCount();

        // Вычисляем раздел
        $resCatalogSection = CIBlockSection::GetList(
            array(),
            array(
                "IBLOCK_CODE"   =>  "clothes",
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
                        onclick="return mywish(this);"></div>
                      <div class="ag-shop-slider-card__likes-count"
                      id="wishid<?= $product["ID"]?>"><?= 
                        $product["wishes"]
                      ?></div>
                    </button>
                      <a class="ag-shop-item-card" href="<?= $product["DETAIL_PAGE_URL"]?>" title="<?= $product["NAME"];?>">
                      <img class="ag-shop-item-card__image" src="<?= $image_url?>">
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
                          <?/*
                            <? for($i=0;$i<round($product["RATING"]);$i++):?>
                            <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                            <? endfor ?>
                            <? for($j=0;$j<5-round($product["RATING"]);$j++):?>
                            <div class="ag-shop-slider-card__rating-item"></div>
                            <? endfor ?>
                          */?>
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
    else:
    ?>
            <div class="grid__col-shrink">
                  <div class="ag-shop-catalog__item">
                  <h2 style="color: rgba(0,122,108,1)">Список желаний пуст</h2>
                  </div>
            </div>
    <?
    endif;
    
    $request = "";
    foreach($_REQUEST as $key=>$value){$request.="$key=$value&";}
    
    ?>
    
    <?if($res->SelectedRowsCount()>($PAGE*$ON_PAGE)):?>
        <input type="hidden" class="catalog-page-input" value="<?= ($PAGE+1);?>"/>
    <?else:?>
    <?endif?>
    
    <?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
