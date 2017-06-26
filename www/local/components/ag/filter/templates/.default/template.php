<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

  <!-- Filter {{{-->
  <form class="ag-shop-filter">
    <div class="ag-shop-filter__filters">
      <div class="ag-shop-filter__filters-item">
        Я хочу 
        <span class="ag-shop-filter__trigger ag-shop-filter__trigger--active" rel="wish-filter" 
        alltitle="всё">
          всё
        </span>
      </div>
      <div class="ag-shop-filter__filters-item">
        интересуюсь 
        <span class="ag-shop-filter__trigger" rel="interests-filter" alltitle="всем">
          всем
        </span>
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
    
    <div class="ag-shop-filter__variants" id="wish-filter">
      <? foreach($arResult["IWANTS"] as $WANT_ID=>$WANT):?>
      <label>
        <input type="checkbox" class="ag-iwant" value="<?= $WANT_ID ?>" title="<?= 
        $WANT["VALUE"]?>">
        <div class="ag-shop-filter__variants-item"><?= $WANT["VALUE"]?></div>
      </label>
      <? endforeach ?>
    </div>
    
    <div class="ag-shop-filter__variants" id="interests-filter">
      <? foreach($arResult["INTERESTS"] as $INTEREST_ID=>$INTEREST):?>
      <label>
        <input type="checkbox" class="ag-interest" value="<?= $INTEREST_ID ?>" title="<?= 
        $INTEREST["VALUE"]?>">
        <div class="ag-shop-filter__variants-item"><?= $INTEREST["VALUE"]?></div>
      </label>
      <? endforeach ?>
    </div>

    <div class="ag-shop-filter__variants filter-passive" id="balls-filter" style="height: 42px;">
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

    
    <div class="ag-shop-filter__confirm filter-passive">
    </div>
    <input type="hidden" id="ag-flag" value="all"/>
    <input type="hidden" id="ag-sorting" value="price-asc"/>
  </form>
  <!-- }}} Filter-->
