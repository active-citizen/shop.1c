<?
    require(
        $_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php"
    );

    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CSearch/CSearch.class.php"
    );
    
    use AGShop\Search as Search;
    $objCSearch = new \Search\CSearch;
    
    foreach($_REQUEST["options"] as $sKey=>$sValue)
        if(!intval($sValue))unset($_REQUEST["options"][$sKey]);
    if(isset($_REQUEST["options"]["FILTER"]) && is_array($_REQUEST["options"]))
        foreach($_REQUEST["options"]["FILTER"] as $sKey=>$sValue)
        if(!intval($sValue))unset($_REQUEST["options"]["FILTER"][$sKey]);
    
    $ON_PAGE = 12;
    $_REQUEST["options"]["PAGE"] = isset($_REQUEST["options"]["PAGE"])?intval($_REQUEST["options"]["PAGE"]):1;
    $PAGE = $_REQUEST["options"]["PAGE"];
    $ON_PAGE = isset($_REQUEST["options"]["LIMIT"])?intval($_REQUEST["options"]["LIMIT"]):$ON_PAGE;
    
    
    $arProducts = $objCSearch->results(
        $_REQUEST["query"],
        $_REQUEST["options"]
    );
    $nTotalCount = $objCSearch->resultsCount;

    foreach($arProducts as $product){
        $product["PROPERTY_MINIMUM_PRICE_VALUE"] = $product["PRICE"];
        $bActive = (
            count($product["OPTIONS"]["AT_STORAGE"])
            &&
            $product["ACTIVE"]=='Y'
            &&
            $product["HIDE_ON_DATE"]=='N'
        );
?>


                <div class="grid__col-shrink" <? if(!$bActive):
                    ?>style="opacity: 0.5"<? endif ?>>
                  <div class="ag-shop-catalog__item">
                    <!-- Обычная карточка товара-->
                    <?/*
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
                    */?>
                      <?
                        // Костыль для ссылок товаров без каталога
                        $arSplitUrl = explode("/",$product["DETAIL_PAGE_URL"]);
                        if(count($arSplitUrl)==4 && $arSplitUrl[1]=='catalog')
                           $product["DETAIL_PAGE_URL"] = "/catalog/root/"
                           .$arSplitUrl[2]."/";
                     ?>
                      <a class="ag-shop-item-card <?= IS_MOBILE ? 'ag-shop-item-card--app' : '' ?>" 
                        <? if($bActive):?>
                        href="<?= 
                        "/catalog/".$product["SECTION_CODE"]."/".$product["CODE"]."/"
                        ?>" 
                        <? endif ?>
                        title="<?= $product["NAME"];?>"
                        style="background-image: url(<?= $product["IMAGE"]?>);" 
                        target="_blank"
                      >
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
    foreach($_REQUEST as $key=>$value){
        if(!is_array($value))
            $request.="$key=$value&";
        else
            foreach($value as $k=>$v)
                if(!is_array($v) && $k!='PAGE')
                    $request.="$key"."[".$k."]=$v&";
                else
                    foreach($v as $k1=>$v1)
                        $request.=$key."[".$k."]"."[".$k1."]=$v1&";
                    
    }
    
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
        <input type="hidden" class="catalog-page-input" value="<?= $request."options[PAGE]=".($PAGE+1);?>"/>
        <div class="grid__col-shrink">
            <div class="ag-shop-catalog__item">
                <a href="#" onclick="return moreSearches(this);" style="">Ещё результаты</a>
            </div>
        </div>
    <?else:?>
    <?endif?>
    
    <?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
    
    
