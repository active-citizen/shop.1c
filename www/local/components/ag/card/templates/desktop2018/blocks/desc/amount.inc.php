 <? if(
    !$noAG  // В статусе активный гражданин?
    &&
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["PROMOCODE"]
        [0]["VALUE_ENUM"]!='да'
    //&&
    //CONTOUR=='test'
    &&
    $arResult['CATALOG_ITEM']["PROPERTIES"]['ARTNUMBER'][0]["VALUE"]!='troyka'
    &&
    $arResult['CATALOG_ITEM']["PROPERTIES"]['ARTNUMBER'][0]["VALUE"]!='parking'
    &&
    !$stopMonLimit
    &&
    !$stopDailyLimit
    &&
    // Если дата мероприятия ещё не вышла
    (
        !$ts1
        ||
        $ts1+24*60*60>time()
    )
    &&
    // Если есть на складе
    count($arResult["OFFERS"][0]["STORAGES"]) 
    // И у тебя достаточно баллов
    &&  
    $arResult["ACCOUNT"]["CURRENT_BUDGET"] >= $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
    // И либо ты активный гражданин, либо на товар
    // установлен рейтинг
    && 
    (
        trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        ||
        trim($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"][0]["VALUE"])
    )
    && (
        !$arResult["AUCTION"]
    )
):?>
  <div class="row">
    <div class="col-2">
        <p class="product-attr">Количество:</p>
    </div>
    <div class="col-4">
        <span>-1+</span>
    </div>
  </div>
<? endif ?>
