<? if(!$arParams["AJAX"]):?>
<div id="productsWrapper" class="desktop-products-wrapper hide-filter">

    <?
    $APPLICATION->IncludeComponent("ag:filter","desktop",$arParams,false);
//    new XPrint($arResult["PRODUCTS"]);
    ?> 

    <section class="catalog-inner">
        <div class="desktop-products-container">
<? endif ?>
            <? foreach($arResult["PRODUCTS"] as $arProduct):?>
            <!-- Product Item -->
            <article class="desktop-product<? if(!$arProduct["EXISTS"]):?> notInStock<? endif?>">
                <button class="desktop-product-favourites wish-on" type="button">
                    <span class="desktop-product-favourites__icon"></span>
                    <span class="desktop-product-favourites__count"><?=
                    $arProduct["WISHES"]?></span>
                </button>
                <a class="desktop-product-link" href="/catalog/<?=
                    $arProduct["SECTION"]["CODE"]
                ?>/<?= 
                    $arProduct["CODE"]
                ?>/">
                    <div class="desktop-product-inner" style="background-image: url('<?= 
                    $arProduct["IMAGE"]
                    ?>')">
                        <!-- Product Title -->
                        <div class="desktop-product-title">
                            <div class="desktop-product-title-wrapper">
                                <div class="middle-aligned">
                                    <h3 class="desktop-product-title__name"><?=
                                        $arProduct["NAME"]
                                    ?></h3>
                                </div>
                            </div>
                        </div>
                        <!-- ============= -->
                        <!-- Product Badge -->
                        <span class="desktop-product-badge">
                            <? if($arProduct["PROPERTY_SPECIALOFFER_VALUE"]):?>
                            <img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
                            <? endif ?>
                            <? if($arProduct["PROPERTY_NEWPRODUCT_VALUE"]):?>
                            <img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
                            <? endif ?>
                            <? if($arProduct["PROPERTY_SALELEADER_VALUE"]):?>
                            <img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
                            <? endif ?>
                        </span>
                        <!-- ============= -->
                        <!-- Product Price -->
                        <div class="desktop-product-price">
                            <div class="desktop-product-price-wrapper">
                                <div class="middle-aligned">
                                    <b class="desktop-product-price__summ"><?=
                                        $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]
                                    ?></b>
                                    <span
                                    class="desktop-product-price__currency"><?=
                                        \Utils\CLang::getPoints(
                                            $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]
                                        )
                                    ?></span>
                                </div>

                            </div>
                        </div>
                        <!-- ============= -->
                        <!-- Product Info -->
                        <div class="desktop-product-info">
                            <div class="desktop-product-info-wrapper">
                                <b class="desktop-product-info__title"><?=
                                $arProduct["NAME"]?></b>
                                <span class="desktop-product-info__category"><?=
                                $arProduct["SECTION"]["NAME"]
                                ?></span>
                                <p class="desktop-product-info__description"><?= 
                                $arProduct["PREVIEW_TEXT"]
                                ?></p>
                            </div>
                        </div>
                        <!-- ============= -->
                        <? if(!$arProduct["EXISTS"]):?>
                        <!-- Product Status -->
                        <div class="desktop-product-status">
                            <div class="desktop-product-status-wrapper">
                                <span class="desktop-product-status__icon"></span>
                                <span class="desktop-product-status__title">
                                    <i>Временно</i>
                                    нет в наличии
                                </span>
                            </div>
                        </div>
                        <!-- ============= -->
                        <? endif ?>
                    </div>
                </a>
            </article>
            <!-- ================= -->
            <? endforeach ?>
    <input type="hidden" name="products" value="<?= implode(",",$arResult["PRODUCT_IDS"])?>">
    <? $nLastItem = ($arResult["PAGE"]-1)*$arResult["ONPAGE"]+count($arResult["PRODUCTS"]); ?>
    <? if($arResult["TOTAL"]>$nLastItem):?>
    <? 
        $arResult["NEXT_PAGE_URL"] .= $arParams["filter"]["only_exists"]
            ?
            "&showProductsAll=111"
            :
            ""
    ?>
    <a href="#" onclick="return desktop_teasers_next_page('<?=
    $arResult["NEXT_PAGE_URL"]?>',<?= $arResult["PAGE"]+1?>);" class="more-button">Ещё<?/*
    <?= $arResult["TOTAL"]- $nLastItem?>*/?></a>
    <? endif ?>
<? if(!$arParams["AJAX"]):?>
        </div>
    </section>

</div>
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

function desktop_teasers_next_page(sUrl,nPageNum){
    $('.more-button').html('Загрузка...');
    $.get("/catalog/index.ajax.php?"+sUrl,function(data){
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
        $('.desktop-products-container').append(data);
        wishes_load();
    })
    
    return false;
}
</script>
