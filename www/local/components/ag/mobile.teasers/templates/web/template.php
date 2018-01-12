<?

$arProducts = $arResult["PRODUCTS"];

/*
echo "<pre>";
print_r($arResult);
echo "</pre>";
*/


foreach($arProducts as $product){
        $product["DETAIL_PAGE_URL"] = "/catalog/"
            .$product["SECTION"]["CODE"]."/"
            .$product["CODE"]."/";

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
        
        $sWant = $product["PROPERTY_WANTS_NAME"];
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
                      <a class="ag-shop-item-card <?= IS_MOBILE ? 'ag-shop-item-card--app' : '' ?>" href="<?= $product["DETAIL_PAGE_URL"]?>" title="<?= $product["NAME"];?>"
                      style="background-image: url(<?= $product["IMAGE"]?>);">
                        <div class="ag-shop-item-card-cover <?= 
                            $sClassName?>"></div>
                        <div class="ag-shop-item-card__points">
                          <div class="ag-shop-item-card__points-count"><?= number_format($product["PROPERTY_MINIMUM_PRICE_VALUE"],0,","," ")?></div>
                          <div class="ag-shop-item-card__points-text"><?= get_points($product["PROPERTY_MINIMUM_PRICE_VALUE"])?></div>
                        </div>
                      <div class="ag-shop-item-card__badges">
                      <?
                      if($product["PROPERTY_NEWPRODUCT_VALUE"]):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__new.png">
                      <? endif?>
                      <?
                      if($product["PROPERTY_SALELEADER_VALUE"]):?>
                        <img class="ag-shop-item-card__badge" src="/local/assets/images/badge__hit.png">
                      <? endif?>
                      <?
                      if($product["PROPERTY_SPECIALOFFER_VALUE"]):?>
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
                          $product["SECTION"]["NAME"];?></p>
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
        if($key=='page')continue;
        $request.="$key=$value&";
    }
    ?>
 
    <input type="hidden" name="products" value="<?= implode(",",$arResult["PRODUCT_IDS"])?>">

    <? if(!count($arProducts)):?>
    <div class="grid__col-shrink">
        <div class="ag-shop-catalog__item">
            <h2 style="text-align:center;color: rgba(0,122,108,1)">Нет товаров,
            <br/>удовлетворяющих <br/>условиям
            фильтра</h2>
        </div>
    </div>
    <? endif ?>

<? $nLastItem = ($arResult["PAGE"]-1)*$arResult["ONPAGE"]+count($arResult["PRODUCTS"]); ?>
<? if($arResult["TOTAL"]>$nLastItem):?>
    <input type="hidden" class="catalog-page-input" value="<?= $request."page=".($arResult["PAGE"]+1);?>"/>
<?else:?>
<?endif?>
 


<?
die;

?>


<? if(!$arResult["PRODUCTS"]):?>
    <div class="empty-products-list">Нет товаров по выбранным Вами условиям</div>
    <? endif ?>
    <? if(!$arParams["AJAX"]):?>
	<main class="mobile-main paddingTop64">
		<div class="mobile-container">
			<div class="mobile-main-wrapper">
				<section class="mobile-search-status">
					<!-- This div is target for autocomplete -->
					<!-- dont remove him -->
				</section>
				<!-- Чтобы сделать большую плитку - добавить к этому контейнеру класс .mobile-product-grid--big -->
				<section class="mobile-product-grid<? if(!$arResult["SMALL_TEASERS"]):?> mobile-product-grid--big<? endif ?>">
    <? endif ?>
                    <? foreach($arResult["PRODUCTS"] as $arProduct):?>
					<article class="mobile-product-item">
								<button class="mobile-product-item-favourite" type="button">
									<span class="mobile-product-item-favourite__icon" productid="<?= $arProduct["ID"]?>" onclick="return mywish(this);"></span>
									<span class="mobile-product-item-favourite__count" id="wishid<?= $arProduct["ID"]?>"><?= $arProduct["WISHES"]?></span>
                                </button>
						<a class="mobile-product-item-wrapper" href="/catalog/<?= $arProduct["SECTION"]["CODE"]?>/<?= $arProduct["CODE"]?>/">
							<div class="mobile-product-item-preview" style="background-image: url('<?= $arProduct["IMAGE"]?>')">
								<span class="mobile-product-item-badge">
                                    <? if($arProduct["PROPERTY_SALELEADER_VALUE"]):?>
									<img class="mobile-product-item-badge__img" src="<?php echo SITE_TEMPLATE_PATH ?>/img/icon__product-label--hit.png" alt="" srcset="<?php echo SITE_TEMPLATE_PATH ?>/img/icon__product-label--hit@2x.png 2x">
                                    <? endif ?>
                                    
                                    <? if($arProduct["PROPERTY_NEWPRODUCT_VALUE"]):?>
									<img class="mobile-product-item-badge__img" src="<?php echo SITE_TEMPLATE_PATH ?>/img/icon__product-label--new.png" alt="" srcset="<?php echo SITE_TEMPLATE_PATH ?>/img/icon__product-label--new@2x.png 2x">
                                    <? endif ?>
                                    
                                    <? if($arProduct["PROPERTY_SPECIALOFFER_VALUE"]):?>
                                    <img class="mobile-product-item-badge__img" src="<?php echo SITE_TEMPLATE_PATH ?>/img/icon__product-label--sale.png" alt="" srcset="<?php echo SITE_TEMPLATE_PATH ?>/img/badge__sale.png 2x">
                                    <? endif ?>
                                    
								</span>
							</div>
							<h3 class="mobile-product-item-title"><?= $arProduct["NAME"]?></h3>
							<span class="mobile-product-item-price">
								<b class="mobile-product-item-price__number"><?= $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]?></b>
								<i class="mobile-product-item-price__currency"><?= get_points($arProduct["PROPERTY_MINIMUM_PRICE_VALUE"])?></i>
							</span>
						</a>
					</article>
                    <? endforeach ?>
                    <input type="hidden" name="products" value="<?= implode(",",$arResult["PRODUCT_IDS"])?>">
                    <? $nLastItem = ($arResult["PAGE"]-1)*$arResult["ONPAGE"]+count($arResult["PRODUCTS"]); ?>
                    <? if($arResult["TOTAL"]>$nLastItem):?>
                    <a href="#" onclick="return teasers_next_page('<?=
                    $arResult["NEXT_PAGE_URL"]?>');" class="more-button">Ещё <?= $arResult["TOTAL"]- $nLastItem?></a>
                    <? endif ?>
    <? if(!$arParams["AJAX"]):?>
				</section>
			</div>
		</div>
	</main>
    <script>wishes_load();</script>
    <? endif ?>
    
    
<script>
function teasers_next_page(sUrl){
    $('.more-button').html('Загрузка...');
    $.get("/catalog/index.mobile.ajax.php?"+sUrl,function(data){
        $('.more-button').remove();
        $('.mobile-product-grid').append(data);
        wishes_load();
    })
    
    return false;
}
</script>
