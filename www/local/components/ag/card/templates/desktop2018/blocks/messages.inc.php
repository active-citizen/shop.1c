<? if($stopMonLimit):?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Ваш месячный лимит заказов данного поощрения исчерпан.
            Ближайшая возможная дата заказа: 
            <b><?= $arResult["NEXT_ORDER"] ?>.</b>
        </p>
      </div>    
    </div>
<? elseif(
    $arResult["ACCOUNT"]["CURRENT_BUDGET"] < 
    $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
    &&
    CUser::IsAuthorized()
): ?>
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Для заказа данного поощрения необходимо набрать 
                <?= 
                    number_format(
                        $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," "
                    )
                ?> 
                <?= 
                    get_points(number_format(
                        $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],
                        0,","," ")
                    )
                ?>.
        </p>
      </div>    
    </div>
<? elseif(
    !CUser::IsAuthorized()
): ?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Для заказа данного поощрения необходимо 
            <a href="http://ag.mos.ru/">авторизоваться</a>
        </p>
      </div>    
    </div>
<? elseif($stopDailyLimit):?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Дневной лимит заказа данного поощрения исчерпан. Попробуйте
            повторить попытку заказа завтра.
        </p>
      </div>    
    </div>
<? elseif(
    (
        
        !trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        &&
        !
        intval($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
            [0]["VALUE"])
        
    )
    ||
    (
        !trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
        &&
        intval(
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
            [0]["VALUE"]
        )
        &&
        (
            $arResult["USER_INFO"]["UF_USER_ALL_POINTS"]
            <
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
                [0]["VALUE"]
        )
        
   )
):?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Обращаем Ваше внимание, что заказать данное поощрение вы
            сможете только после получения статуса &laquo;Активный
            гражданин&raquo;. Статус присваивается пользователям,
            набравшим <?= $arParams["ALL_POINTS_LIMIT"]?> баллов
        </p>
      </div>    
    </div>
<? elseif($arResult["AUCTION"] 
&& $arResult["AUCTION"]["IS_FINISHED"]):?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Заказ данного поощрения происходил по 
            <a href="/rules/hiw/#auction">правилам аукциона</a>
            &#160;c  <span class="date"><?= $arResult["AUCTION"]["START_DATE"] ?></span>,
            по
            <span class="date"><?= $arResult["AUCTION"]["END_DATE"] ?></span> 
        </p>
      </div>    
    </div>
<? elseif($arResult["AUCTION"] &&
!$arResult["AUCTION"]["IS_CURRENT"] &&
!$arResult["AUCTION"]["IS_FINISHED"]):?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Заказ данного поощрения будет происходить по 
            <a href="/rules/hiw/#auction">правилам аукциона</a>. Начало
            торгов  <span class="date"><?= $arResult["AUCTION"]["START_DATE"] ?></span>,
            завершение торгов
            <span class="date"><?= $arResult["AUCTION"]["END_DATE"] ?></span> 
        </p>
      </div>    
    </div>
<? elseif($arResult["AUCTION"] && $arResult["AUCTION"]["IS_CURRENT"]):?>
    <div class="col-12">
      <div class="warning-border">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Заказ данного поощрения происходит по 
            <a href="/rules/hiw/#auction">правилам аукциона</a>. Завершение торгов
            <span class="date"><?= $arResult["AUCTION"]["END_DATE"] ?></span> 
        </p>
      </div>    
    </div>
<? endif ?>

