    <? if(!$arResult["PRODUCTS"]):?>
    <div class="mobile-container">
            <div class="mobile-main-wrapper">
                <section class="mobile-search-status">
                    <!-- This div is target for autocomplete -->
                    <!-- dont remove him -->
                </section>
                <section class="mobile-search-notfind">
                    <div class="mobile-search-notfind-wrapper">
                        <span class="icon-search-notfind"></span>
                        <p class="default-paragraph mobile-search-result__info">
                            <span>Нет товаров по выбранным Вами условиям</span> 
                        </p>
                    </div>
                </section>
            </div>
        </div>
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
                    <? $nKey = 0;?>
                    <? foreach($arResult["PRODUCTS"] as $arProduct):?>
                    <? $nKey++;?>
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
                    <? if(
                        $nKey 
                        && ($nKey % $arParams["pagination"]["onpage"]==0)
                    ):?>
                        <a name="PAGE-<?=
                        floor($nKey/$arParams["pagination"]["onpage"])+1
                        ?>"></a>
                    <? endif ?>
                    <? endforeach ?>
                    <input type="hidden" name="products" value="<?= implode(",",$arResult["PRODUCT_IDS"])?>">
                    <? $nLastItem = ($arResult["PAGE"]-1)*$arResult["ONPAGE"]+count($arResult["PRODUCTS"]); ?>
                    <? if($arResult["TOTAL"]>$nLastItem):?>
                    <a href="#" onclick="return teasers_next_page('<?=
                    $arResult["NEXT_PAGE_URL"]?>',<?= $arResult["PAGE"]+1?>);" class="more-button">Ещё<?/*
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

<? if(!$arParams["AJAX"]):?>
teasersRewind();
<? endif ?>

$(document).ready(function(){
});

function teasersRewind(){
    /*
    var hash = document.location.hash;
    hash = hash.replace('#','');
    var descriptor = 'a[name="'+hash+'"]';
    if($('a[name="'+hash+'"]').length){
        var destination = $('a[name="'+hash+'"]').offset().top+300;
        $('body').animate({ scrollTop: destination }, 110);
    }
    */
}

function teasers_next_page(sUrl,nPageNum){
    $('.more-button').html('Загрузка...');
    $.get("/catalog/index.mobile.ajax.php?"+sUrl,function(data){
        var search = document.location.search;
        var re=/^(.*)\/$/
        search = search.replace(/[&\?]page=\d+/,'');
        search = search.replace(re,"$1");
        console.log(search);
        if(search=='') 
            newsearch = search+'?page='+nPageNum+'/'
        else
            newsearch = search+'&page='+nPageNum+'/';
        window.history.replaceState({}, search, newsearch);
        //document.location.hash = "PAGE-"+nPageNum;
        $('.more-button').remove();
        $('.mobile-product-grid').append(data);
        wishes_load();
    })
    
    return false;
}
</script>
