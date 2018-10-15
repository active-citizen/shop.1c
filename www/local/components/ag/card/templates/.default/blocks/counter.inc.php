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
  <div class="grid grid--bleed amounter amounter<? if(
    count($arResult["OFFERS"][0]["STORAGES"])==1 &&
    count($arResult["OFFERS"])==1):
  ?>--on<? else: ?>--off<? endif ?>">
    <span id="mon-limit" style="display:none"><?=
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"]
        -
        $arResult["MON_ORDERS"]
    ?></span>

    <div class="grid__col-shrink" style="display:none;">
      <div class="ag-shop-card__field ag-shop-card__field--align-right">
        <div class="ag-shop-card__fieldname">Единица:</div>
        <div class="ag-shop-card__total-points"><?= $arResult["CATALOG_ITEM"]["PROPERTIES"]["QUANT"][0]["VALUE"] ?></div>
      </div>
    </div>
      
    <div class="grid__col-auto">
      <div class="ag-shop-card__field">
        <div class="ag-shop-card__fieldname">Количество:</div>
        <div class="ag-shop-card__count">
          <button class="ag-shop-card__count-button ag-shop-card__count-button--sub" type="button"></button>
          <div style="padding-top: 3px;" class="ag-shop-card__count-number">1</div>
          <button class="ag-shop-card__count-button ag-shop-card__count-button--add" type="button">
              <div class="ag-shop-modal__alert"
              id="counter-hint" class="counter-hint"
              style="display: none"><i class="ag-shop-icon
              ag-shop-icon--attention"></i><span></span></div>
          </button>
        </div>
      </div>
    </div>
    
    <div class="grid__col-shrink">
      <div class="ag-shop-card__field ag-shop-card__field--align-right">
        <div class="ag-shop-card__fieldname">Итого:</div>
        <div id="ag-shop-card__total-points" class="ag-shop-card__total-points"><?= number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,",","")?></div>
      </div>
    </div>
  </div>
  <? endif ?>

