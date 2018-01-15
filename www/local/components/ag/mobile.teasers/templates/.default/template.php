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
                    $arResult["NEXT_PAGE_URL"]?>');" class="more-button">Ещё<?/*
                    <?= $arResult["TOTAL"]- $nLastItem?>*/?></a>
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
