<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");

    // Забираем сохранённые фильтры для этой страницы из сессии
    $arSessionFilter = $_SESSION["FILTERS"][$_SERVER["REQUEST_URI"]];
    $arSessionSorting = $_SESSION["SORTINGS"][$_SERVER["REQUEST_URI"]];

    // Хочу
    $sHashString = "filter_iwant="
        .implode(",",$arSessionFilter["PROPERTY_WANTS"]);
    // Интересуюсь
    $sHashString .= "&filter_interest="
        .implode(",",$arSessionFilter["PROPERTY_INTERESTS"]);

    // ДИапазон цен
    if(
        isset($arSessionFilter["<=PROPERTY_MINIMUM_PRICE"])
        &&
        isset($arSessionFilter[">=PROPERTY_MINIMUM_PRICE"])
    )
        $sHashString .= "&filter_balls="
            .$arSessionFilter[">=PROPERTY_MINIMUM_PRICE"]
            .","
            .$arSessionFilter["<=PROPERTY_MINIMUM_PRICE"];
    else
        $sHashString .= "&filter_balls=undefined";

    // Новое
    if(
        isset($arSessionFilter["PROPERTY_NEWPRODUCT"])
        &&
        $arSessionFilter["PROPERTY_NEWPRODUCT"]
    )
        $sHashString .= "&flag=news";

    // Хит
    elseif(
        isset($arSessionFilter["PROPERTY_SALELEADER"])
        &&
        $arSessionFilter["PROPERTY_SALELEADER"]
    )
        $sHashString .= "&flag=populars";

    // Акция
    elseif(
        isset($arSessionFilter["PROPERTY_SPECIALOFFER"])
        &&
        $arSessionFilter["PROPERTY_SPECIALOFFER"]
    )
        $sHashString .= "&flag=actions";

    else
        $sHashString .= "&flag=all";

    // Сортировки
    if(
        isset($arSessionSorting["PROPERTY_MINIMUM_PRICE"]) 
        && 
        $arSessionSorting["PROPERTY_MINIMUM_PRICE"]=='ASC'
    )
        $sHashString .= "&sorting=price-asc";
    elseif(
        isset($arSessionSorting["PROPERTY_MINIMUM_PRICE"]) 
        && 
        $arSessionSorting["PROPERTY_MINIMUM_PRICE"]=='DESC'
    )
        $sHashString .= "&sorting=price-desc";
    elseif(
        isset($arSessionSorting["PROPERTY_RATING"]) 
        && 
        $arSessionSorting["PROPERTY_RATING"]=='DESC'
    )
        $sHashString .= "&sorting=rating-desc";
        

?>
<script>
document.location.hash = '#<?= $sHashString;?>';
</script>


  <!-- Filter {{{-->
  <? if(!IS_MOBILE):?>
  <form class="ag-shop-filter desktop-filter">
    <!-- 
    <div class="ag-shop-filter__filters">
    <!--
    <div class="filter-clear" onclick="return filter_clear();" title="Очистить фильтр"></div>

    <? if($arResult["IWANTS"]):?>
      <div class="ag-shop-filter__filters-item">
        Я хочу 
        <span class="ag-shop-filter__trigger ag-shop-filter__trigger--active" rel="wish-filter" 
        alltitle="всё">
          всё
        </span>
      </div>
    <? endif ?>
      <div class="ag-shop-filter__filters-item">
    <? if($arResult["INTERESTS"]):?>
        интересуюсь 
        <span class="ag-shop-filter__trigger" rel="interests-filter" alltitle="всем">
          всем
        </span>
    <? endif ?>
      </div>
      <div class="ag-shop-filter__filters-item">
        <? if($USER->IsAuthorized()):?>
        у меня 
        <span class="ag-shop-filter__trigger" rel="balls-filter" alltitle="<?= 
        $arResult["myBalls"] ?>">
          <?= $arResult["myBalls"] ?> 
        </span>
        <?else:?>
        <span class="ag-shop-filter__login ag-shop-filter__trigger--active" 
            onclick="document.location.href='<?= 
            preg_replace("#(https?://.*?/).*#","$1",CONTOUR_URL)
            ?>'">
          <a style="text-decoration: none;" class="ag-shop-filter__trigger--active" href="<?= 
          preg_replace("#(https?://.*?/).*#","$1",CONTOUR_URL)
          ?>">
            Войти
          </a>
        </span>
        <?endif?>
      </div>
    </div>
      -->
   
    <? if($arResult["IWANTS"]):?>
    <div class="ag-shop-filter__variants" id="wish-filter">
      <? foreach($arResult["IWANTS"] as $WANT_ID=>$WANT):?>
      <label>
        <input type="checkbox" class="ag-iwant" value="<?= $WANT_ID ?>" title="<?= 
        $WANT["NAME"]?>">
        <div class="ag-shop-filter__variants-item">
            <?= $WANT["NAME"]?> 
            <? if($WANT["COUNT"]):?>
            (<?=
                $WANT["COUNT"]
            ?>)
            <? endif ?>
        </div>
      </label>
      <? endforeach ?>
    </div>
    <? endif ?>
    
    <? if($arResult["INTERESTS"]):?>
    <div class="ag-shop-filter__variants" id="interests-filter" style="display:
    block; text-align: center;">
      <? foreach($arResult["INTERESTS"] as $INTEREST_ID=>$INTEREST):?>
      <label>
        <input type="checkbox" class="ag-interest" value="<?= $INTEREST_ID ?>" title="<?= 
        $INTEREST["NAME"]?>">
        <div class="ag-shop-filter__variants-item">
            <?= $INTEREST["NAME"]?> 
            <? if($INTEREST["COUNT"]):?>
            (<?=
            $INTEREST["COUNT"]
            ?>)
            <? endif ?>
        </div>
      </label>
      <? endforeach ?>
    </div>
    <? endif ?>

    <div class="ag-shop-filter__variants filter-passive" id="balls-filter"
    style="min-height: 42px;">
        <? $pureBalls = str_replace(" ","",$arResult["MY_BALLS"]);?>
        <? $arBallsLimits=
        array(500,1000,1500,2000,2500,3000,15000);?>
        <? foreach($arBallsLimits as $endBalls):
        if($endBalls>intval(preg_replace("#[^\d]#","",$arResult["myBalls"])))continue;
        ?>
          <label>
            <input type="checkbox" name="ag-balls" class="ag-balls"
            value="0,<?= $endBalls ?>" title="до <?= $endBalls ?> баллов">
            <div class="ag-shop-filter__variants-item">до <?= $endBalls ?> баллов</div>
          </label>
        
        <? endforeach?>
          <label>
            <input type="checkbox" name="ag-balls" class="ag-balls"
            value="0,<?= preg_replace("#[^\d]#","",$myBalls) ?>" title="до <?=
            $arResult["myBalls"] ?>">
            <div class="ag-shop-filter__variants-item">до <?=
            $arResult["myBalls"] ?></div>
          </label>
     </div>

    <!-- 
    <div class="ag-shop-filter__confirm filter-passive">
    </div>
    -->
    <input type="hidden" id="ag-flag" value="all"/>
    <input type="hidden" id="ag-sorting" value="price-asc"/>
  </form>
  <? endif ?>
  <!-- }}} Filter-->
