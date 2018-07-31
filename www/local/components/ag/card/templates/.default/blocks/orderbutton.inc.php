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
    (
        !$arResult["AUCTION"]
    )
):?>
<button class="ag-shop-card__submit-button" onclick="return productConfirm();" 
    type="button">Заказать за <strong><?= 
        number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," ")
    ?></strong> <span><?=
    get_points($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"])?></span></button>
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
<button class="ag-shop-card__submit-button" onclick="return setBet();" 
    type="button">Сделать ставку</button>
<? elseif(
    $arResult["AUCTION"]
    &&
    (
        $arResult["AUCTION"]["IS_CURRENT"] 
        ||
        $arResult["AUCTION"]["IS_FINISHED"] 
    )
    &&
    $arResult["BET"]
):?>
  <div class="ag-shop-card__container">
    <? if($arResult["BET"]["STATUS"]=='reject'):?>
        <h3 class="reject">
            Ваша ставка отклонена.
            <div>
            Причина: <?= $arResult["BET"]["COMMENT"] ?>
            </div>
        </h3>
    <? endif?>
    <div class="ag-shop-card__requirements">
    Вы сделали ставку.<br/>
    Дата ставки: <?= $arResult["BET"]["CTIME"]?><br/>
    Предложенная цена: <?= $arResult["BET"]["PRICE"]?> <?=
    get_points($arResult["BET"]["PRICE"])?><br/>
    Заявленное количество: <?= $arResult["BET"]["AMOUNT"]?><br/>
    Общая сумма ставки: <?=
    $arResult["BET"]["PRICE"]*$arResult["BET"]["AMOUNT"] ?> <?=
    get_points($arResult["BET"]["PRICE"]*$arResult["BET"]["AMOUNT"])
    ?><br/>
    Заявленное получение: <?= $arResult["BET"]["STORE"]["TITLE"]?><br/>
    <br/>
    <? /*print_r($arResult["BET"])*/?> 
    <? if($arResult["BET"]["STATUS"]!='reject'):?>
    Ожидайте окончания торгов
    <? endif ?>
    </div>
  </div>
<? elseif(
    $arResult["AUCTION"]
    &&
    $arResult["AUCTION"]["IS_CURRENT"]
    &&
    $arResult["BET"]
):?>
Вы сделали ставку
<? print_r($arResult["BET"])?>. Торги окончены. Ожидайте
подведения итогов
 <? endif ?>

