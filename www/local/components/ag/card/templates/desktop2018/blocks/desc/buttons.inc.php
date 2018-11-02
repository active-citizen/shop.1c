              <div class="product-description-btn">
                <div class="row">
<? if(
    !$noAG
    &&
    !$stopMonLimit
    &&
    !$stopDailyLimit
    &&
    // Если дата мероприятия не вышла
    (
        !$ts1    // НЕ определена дата мероприятия
        ||
        $ts1+24*60*60>time()    // Дата мероприятия не вышла
    )
    &&
    // Если есть на складе
    count($arResult["OFFERS_STORAGES"]) 
    &&  
    // Если достаточно средств
    $arResult["ACCOUNT"]["CURRENT_BUDGET"] >= $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
    &&  
    (
        trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        ||
        trim($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"][0]["VALUE"])
    )
    && 
    (
        !$arResult["AUCTION"]
    )
):?>
  <div class="col-7">
    <button type="button" class="btn btn-primary btn-lg">
        Заказать за <?= 
        number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," ")
        ?> <?= 
        get_points($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"])
        ?>
    </button>
  </div>
<? elseif(
    !$noAG
    &&
    !$stopMonLimit
    &&
    !$stopDailyLimit
    &&
    // Если дата мероприятия не вышла
    (
        !$ts1    // НЕ определена дата мероприятия
        ||
        $ts1+24*60*60>time()    // Дата мероприятия не вышла
    )
    &&
    // Если есть на складе
    count($arResult["OFFERS"][0]["STORAGES"]) 
    &&  
    // Если достаточно средств
    $arResult["ACCOUNT"]["CURRENT_BUDGET"] >= $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
    &&  
    (
        trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        ||
        trim($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"][0]["VALUE"])
    )
    &&
    $arResult["AUCTION"]
    &&
    $arResult["AUCTION"]["IS_CURRENT"]
    &&
    !$arResult["BET"]
):?>
                  <div class="col-7">
                    <button type="button" class="btn btn-primary btn-lg">
                        Сделать ставкув
                    </button>
                  </div>
<? endif ?>
                  <div class="col-2 col-sm-auto">
                      <a href="#"><i id="product-heart" class="far fa-heart"></i></a>
                  </div>
                  <div class="col-2 col-sm-1">
                    <a href="#"><i id="product-share" class="fas fa-share-alt"></i></a>
                  </div>
                </div>
              </div>

