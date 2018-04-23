<aside class="desktop-products-filter">
    <form id="desktopCatalogFilterForm" class="desktop-products-filter-form"
    name="desktopCatalogFilterForm" action="#teasers">
        <input id="sorting" name="sorting" type="hidden" value="<?=
        $_REQUEST["sorting"]?htmlspecialchars($_REQUEST["sorting"]):"fresh-desc"?>">
        <input id="smallicons" name="smallicons" type="hidden" value="<?=
        $_REQUEST["smallicons"]?htmlspecialchars($_REQUEST["smallicons"]):0?>">
        <input id="not_exists" name="not_exists" type="hidden" value="<?=
        $_REQUEST["not_exists"]?1:0?>">
        <?if($arParams["filter"]["section_code"]):?>
        <input type="hidden" name="section_code" value="<?=
        $arParams["filter"]["section_code"]?>">
        <? endif ?>
        <div class="desktop-products-filter-form-wrapper">
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Интересы</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestAll" 
                        class="desktop-checkbox__input defaultCheck filterCheckboxAll" 
                        type="checkbox" name="interestAll" value="0" <? 
                        if(!$arParams["filter"]["interest"]):?>checked<? endif?>>
                        <label for="interestAll" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Все</span>
                        </label>
                    </div>
                    <? foreach($arResult["INTERESTS"] as $arInterest):?>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestAG-<?= $arInterest["ID"]?>"
                        class="desktop-checkbox__input all-checked"
                        type="checkbox" name="interestAG-<?= $arInterest["ID"]?>" value="<?= $arInterest["ID"]?>"
                        <? if(in_array($arInterest["ID"],
                        $arParams["filter"]["interest"])):?>checked<? endif?>>
                        <label for="interestAG-<?= $arInterest["ID"]?>" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title"><?= $arInterest["NAME"]?></span>
                        </label>
                    </div>
                    <? endforeach ?>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Цена</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-products-filter-price">
                        <div class="default-desktop-input-wrapper">
                            <input class="default-desktop-input defaultClear" type="number" 
                            name="productPriceMin" maxlength="5" pattern="[0-9]{,5}"
                            value="<?= $arParams["filter"]["price_min"]?>"
                            >
                        </div>
                        <div class="default-desktop-input-wrapper">
                            <input class="default-desktop-input defaultClear" type="number" 
                            name="productPriceMax" maxlength="5" pattern="[0-9]{,5}"
                            value="<?= $arParams["filter"]["price_max"]?>"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Где получать</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="deliveryAll" 
                        class="desktop-checkbox__input defaultCheck filterCheckboxAll" 
                        type="checkbox" name="deliveryAll" value="0" <? 
                        if(!$arParams["filter"]["store"]):?>checked<? endif?>
                        >
                        <label for="deliveryAll" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Все</span>
                        </label>
                    </div>
                    <? foreach($arResult["STORES"] as $arStore):?>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="delivery<?= $arStore['CODE']?>"
                        class="desktop-checkbox__input all-checked" type="checkbox"
                        name="delivery<?= $arStore['CODE']?>" value="<?=
                        $arStore['ID']?>" <? 
                        if(in_array($arStore["ID"],$arParams["filter"]["store"])):
                        ?>checked<? endif?>>
                        <label for="delivery<?= $arStore['CODE']?>" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title"><?=
                            $arStore['TITLE']?></span>
                        </label>
                    </div>
                    <? endforeach ?>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <? /*
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Рейтинг</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-products-filter-rating">
                        <div class="desktop-checkbox desktop-checkbox-round">
                            <input id="ratingStars" class="desktop-checkbox__input" type="checkbox" name="ratingStars" value="531">
                            <label for="ratingStars" class="desktop-checkbox__label">
                                <span class="desktop-checkbox__icon desktop-checkbox__icon--stars"></span>
                                <span class="desktop-checkbox__title">и более</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Размеры</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXXS" class="desktop-checkbox__input" type="checkbox" name="sizeXXS" value="611">
                        <label for="sizeXXS" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XXS</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXS" class="desktop-checkbox__input" type="checkbox" name="sizeXS" value="602" checked>
                        <label for="sizeXS" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XS</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeS" class="desktop-checkbox__input" type="checkbox" name="sizeS" value="603">
                        <label for="sizeS" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">S</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeM" class="desktop-checkbox__input" type="checkbox" name="sizeM" value="604">
                        <label for="sizeM" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">M</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeL" class="desktop-checkbox__input" type="checkbox" name="sizeL" value="605">
                        <label for="sizeL" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">L</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXL" class="desktop-checkbox__input" type="checkbox" name="sizeXL" value="606">
                        <label for="sizeXL" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XL</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXXL" class="desktop-checkbox__input" type="checkbox" name="sizeXXL" value="607">
                        <label for="sizeXXL" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XXL</span>
                        </label>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Цвет</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorBlack" class="desktop-checkbox__input" type="checkbox" name="colorBlack" value="511" checked>
                        <label for="colorBlack" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Черный</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorWhite" class="desktop-checkbox__input" type="checkbox" name="colorWhite" value="502">
                        <label for="colorWhite" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Белый</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorGreen" class="desktop-checkbox__input" type="checkbox" name="colorGreen" value="503">
                        <label for="colorGreen" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Зеленый</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorPink" class="desktop-checkbox__input" type="checkbox" name="colorPink" value="504">
                        <label for="colorPink" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Розовый</span>
                        </label>
                    </div>
                </div>
            </div>
            */?>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Показывать</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsAll"
                        class="desktop-checkbox__input defaultCheck" 
                        type="checkbox" name="showProductsAll" value="111" 
                        <? if(!$arParams['filter']['not_exists']):?>checked<? endif ?>                        
                        >
                        <label for="showProductsAll" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">В наличии</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsHit"
                        class="desktop-checkbox__input defaultReset" 
                        type="checkbox" name="showProductsHit" value="002"
                        <? if($arParams['filter']['hit']):?>checked<? endif ?>
                        >
                        <label for="showProductsHit" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Хит</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsSale"
                        class="desktop-checkbox__input defaultReset" 
                        type="checkbox" name="showProductsSale" value="003"
                        <? if($arParams['filter']['sale']):?>checked<? endif ?>                        
                        >
                        <label for="showProductsSale" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Акция</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsNew"
                        class="desktop-checkbox__input defaultReset" 
                        type="checkbox" name="showProductsNew" value="004"
                        <? if($arParams['filter']['new']):?>checked<? endif ?>                        
                        >
                        <label for="showProductsNew" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Новое</span>
                        </label>
                    </div>
                </div>
            </div>
            <!-- ============== -->

            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-form-actions">
                    <button id="desktopProductsFilterSubmit" 
                    class="btn-filter-form btn-filter-form--submit" 
                    type="submit" 
                    name="filter"
                    onclick="return applyFilter();"
                    >Применить</button>
                    <button id="desktopProductsFilterReset" class="btn-filter-form btn-filter-form--reset" type="reset" name="desktopProductsFilterReset">Сбросить</button>
                </div>
            </div>

        </div>
    </form>
</aside>

<script>
/**
    Применение фильтра без 
*/
function applyFilter(){
    // Определяем базовую строку запроса
    var query = '<?= 
        $arParams["filter"]["wishes_user"]
        ?
        '/profile/wishes/index.ajax.php?form=filter'
        :
        '/catalog/index.ajax.php?form=filter'
    ?>';
    // Добавляем поля из формы
    $('#desktopCatalogFilterForm input').each(function(){
        if($(this).attr("type")=='checkbox' && $(this).is(':checked'))
            query+="&"+$(this).attr("name")+'='+$(this).val();
        else if($(this).attr("type")=='checkbox')
            query+="";
        else
            query+="&"+$(this).attr("name")+'='+$(this).val();
    });

    var nPageNum = 1;
    $('.desktop-products-container').addClass('teaser-loading');
    // Отправляем запрос на получение 1-й страницы
    $.get(query,function(data){
        var search = query;
        var re=/^(.*)\/$/;
        // Узнаём раздел

        search = search.replace(/^.*(\?.*)$/,'$1');
        search = search.replace(/[&\?]page=\d+/,'');
        search = search.replace(re,"$1");

        if(search=='') 
            newsearch = search+'?page='+nPageNum+'/'
        else
            newsearch = search+'&page='+nPageNum+'/';
        console.log(newsearch);
        window.history.replaceState({}, search, newsearch);
        //document.location.hash = "PAGE-"+nPageNum;
        $('.more-button').remove();
        $('.desktop-products-container').html(data);
        $('.desktop-products-container').removeClass('teaser-loading');
        wishes_load();
    })
     
    return false;
}
</script>
