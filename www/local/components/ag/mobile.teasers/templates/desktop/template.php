<? if(!$arParams["AJAX"]):?>
<div id="productsWrapper" class="desktop-products-wrapper<?
if(!$_SERVER["QUERY_STRING"]):?> hide-filter<? endif ?>">
    <a name="teasers"></a>

    <!-- Компонент фильтров -->
    <?
    $APPLICATION->IncludeComponent("ag:filter","desktop",$arParams,false);
    ?> 
    <!-- Конец: Компонент фильтров -->

    <section class="catalog-inner">
        <div class="desktop-products-container<? 
        if($arParams["smallicons"]==1):?> desktop-products-container--gridSmall<? 
        endif?><? 
        if($arParams["smallicons"]==2):?> desktop-products-container--gridList<? 
        endif?>">
<? endif ?>
        <? if(count($arResult["PRODUCTS"])):?>
            <? foreach($arResult["PRODUCTS"] as $arProduct):?>
            <!-- Product Item -->
            <article class="desktop-product<? if(!$arProduct["EXISTS"]):?> notInStock<? endif?>">
                <button class="desktop-product-favourites" type="button"
                productid="<?= $arProduct["ID"]?>"
                onclick="return mywish(this);">
                    <span class="desktop-product-favourites__icon"></span>
                    <span class="desktop-product-favourites__count"
                    id="wishid<?= $arProduct["ID"]?>"><?=
                    intval($arProduct["WISHES"])?></span>
                </button>
                <a class="desktop-product-link" href="/catalog/<?=
                    $arProduct["SECTION"]["CODE"]
                ?>/<?= 
                    $arProduct["CODE"]
                ?>/">
                    <div class="desktop-product-inner" style="background-image: url('<?= 
                    $arProduct["IMAGE"]
                    ?>')">
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
                        <!-- Product Title -->
                        <div class="desktop-product-title" title="<?= $arProduct["NAME"]; ?>">
                            <div class="desktop-product-title-wrapper">
                                <div class="middle-aligned">
                                    <h3 class="desktop-product-title__name"><?=
                                        $arProduct["NAME"]
                                    ?></h3>
                                </div>
                            </div>
                        </div>
                        <!-- Product Details -->
                        <div class="desktop-product-details">
                            <div class="desktop-product-details-description">
                            <?= cardTextClear($arProduct["PREVIEW_TEXT"]); ?>
                            </div>
                        </div>

                        <!-- ============= -->
                        <!-- Product Badge -->
                        <span class="desktop-product-badge">
                            <? if($arProduct["PROPERTY_SPECIALOFFER_VALUE"]):?>
                            <img class="desktop-product-badge__img" src="<?=
                            SITE_TEMPLATE_PATH
                            ?>/img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
                            <? endif ?>
                            <? if($arProduct["PROPERTY_NEWPRODUCT_VALUE"]):?>
                            <img class="desktop-product-badge__img" src="<?=
                            SITE_TEMPLATE_PATH
                            ?>/img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
                            <? endif ?>
                            <? if($arProduct["PROPERTY_SALELEADER_VALUE"]):?>
                            <img class="desktop-product-badge__img" src="<?=
                            SITE_TEMPLATE_PATH
                            ?>/img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
                            <? endif ?>
                        </span>
                                <!-- ============= -->
                        <!-- Product Info -->
                        <div class="desktop-product-info">
                            <div class="desktop-product-info-wrapper">
                                <b class="desktop-product-info__title"><?=
                                $arProduct["NAME"]?></b>
                                <span class="desktop-product-info__category"><?=
                                $arProduct["SECTION"]["NAME"]
                                ?></span>
                                <p class="desktop-product-info__description"><? 
                                echo cardTextClear($arProduct["PREVIEW_TEXT"])
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
            <div class="catalog-inner-more more-button">
            <a href="#" onclick="return desktop_teasers_next_page('<?=
            $arResult["NEXT_PAGE_URL"]?>',<?= $arResult["PAGE"]+1?>);"
            class="catalog-inner-more__btn">Ещё<?/*
            <?= $arResult["TOTAL"]- $nLastItem?>*/?></a>
            </div>
            <? endif ?>
        <? else:?>
            <div class="filter-empty-alert">
                Не найдено поощрений, удовлетворяющих условиям фильтра.
                Попробуйте изменить его условия.
            </div>
        <? endif?>
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

    var startUrl = '<?= 
        $arParams["filter"]["wishes_user"]
        ?
        "/profile/wishes/index.ajax.php?"
        :
        "/catalog/index.ajax.php?"
    ?>';

    $('.more-button').html('Загрузка...');
    $.get(startUrl+sUrl,function(data){
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
        truncTitle();
    })
    
    return false;
}
</script>

<?


    /**
        Очиска текста товара от лишних cстилей для достижения единообразия
        вёрстки 
    */
    function cardTextClear($text){
        

        $text =  str_replace(
            "\n","",
            $text 
        );
       
        $text =   preg_replace(
            "#\s+#"," ",
            $text
        );

        $text =   preg_replace(
            "#>\s+<#","><",
            $text 
        );
          
        $text =   preg_replace(
            "/style=\".*?\"/i", "",
            $text 
        );
        
       
        $text =   preg_replace(
            "/<br.*?>/i", "",
            $text 
        );

        $text =   preg_replace(
            "#<p>\s+&nbsp;</p>#", "",
            $text 
        );

        $text =   preg_replace(
            "#>\s+#", ">",
            $text 
        );

        $text =   preg_replace(
            "#>\(#", "> (",
            $text 
        );

        $text =   preg_replace(
            "#<div> &nbsp;</div>#", "",
            $text 
        );

        $text = preg_replace(
            "#<a.*?>.*?</a>#", "", $text
        );
 
        return $text;
    }
