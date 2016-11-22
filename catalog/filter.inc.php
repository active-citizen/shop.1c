<?
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"WANTS"));
    $IWANTS = array();
    while($iwant = $res->getNext())$IWANTS[$iwant["ID"]]=$iwant;
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"INTERESTS"));
    $INTERESTS = array();
    while($interest = $res->getNext())$INTERESTS[$interest["ID"]]=$interest;
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"TYPES"));
    $TYPES = array();
    while($type = $res->getNext())$TYPES[$type["ID"]]=$type;

?>
          <!-- Filter {{{-->
          <form class="ag-shop-filter">
            <div class="ag-shop-filter__filters">
              <div class="ag-shop-filter__filters-item">
                Я хочу 
                <span class="ag-shop-filter__trigger ag-shop-filter__trigger--active" rel="wish-filter" alltitle="всё">
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
                <span class="ag-shop-filter__trigger" rel="balls-filter">
                  <?= $myBalls ?> 
                </span>
                <?else:?>
                <span class="ag-shop-filter__login ag-shop-filter__trigger--active" 
                    onclick="document.location.href='<? 
                    if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru')
                        echo "http://ag.mos.ru/site/";
                    elseif($_SERVER["HTTP_HOST"]=='dev.shop.ag.mos.ru')
                        echo "http://testing.ag.mos.ru/site/";
                    else
                        echo "http://testing.ag.mos.ru/site/";
                  ?>'">
                  Войти
                </span>
                <?endif?>
              </div>
            </div>
            
            <div class="ag-shop-filter__variants" id="wish-filter">
              <? foreach($IWANTS as $WANT_ID=>$WANT):?>
              <label>
                <input type="checkbox" class="ag-iwant" value="<?= $WANT_ID ?>" title="<?= $WANT["VALUE"]?>">
                <div class="ag-shop-filter__variants-item"><?= $WANT["VALUE"]?></div>
              </label>
              <? endforeach ?>
            </div>
            
            <div class="ag-shop-filter__variants" id="interests-filter">
              <? foreach($INTERESTS as $INTEREST_ID=>$INTEREST):?>
              <label>
                <input type="checkbox" class="ag-interest" value="<?= $INTEREST_ID ?>" title="<?= $INTEREST["VALUE"]?>">
                <div class="ag-shop-filter__variants-item"><?= $INTEREST["VALUE"]?></div>
              </label>
              <? endforeach ?>
            </div>

            <div class="ag-shop-filter__variants filter-passive" id="balls-filter" style="height: 42px;">
                <? $pureBalls = str_replace(" ","",$MY_BALLS);?>
                <? for($i=0;pow(10,$i)<$pureBalls;$i++):?>
                  <?
                    $startBalls = pow(10,$i)+1; 
                    if($startBalls==2)$startBalls=0;
                    $endBalls = pow(10,$i+1); 
                    if($endBalls>$pureBalls) $endBalls=$pureBalls;
                  ?>
                  <label>
                    <input type="radio" name="ag-balls" class="ag-balls" value="<?= $startBalls ?>,<?= $endBalls ?>" title="от <?= $startBalls ?> до <?= $endBalls ?> баллов">
                    <div class="ag-shop-filter__variants-item">от <?= $startBalls ?> до <?= $endBalls ?> баллов</div>
                  </label>
                
                <? endfor?>
                  <label>
                    <input 
                        <? if($USER->IsAuthorized()):?>checked<? endif ?>
                        type="radio" 
                        name="ag-balls" class="ag-balls" 
                        value="0,<?= $pureBalls?>" 
                        title="от 0 до <?= $pureBalls?$pureBalls:0 ?> <?= get_points($pureBalls)?>"
                    >
                    <div class="ag-shop-filter__variants-item">от 0 до <?= $endBalls?$endBalls:0 ?> <?= get_points($pureBalls)?></div>
                  </label>
                  <label>
                    <input 
                        <? if(!$USER->IsAuthorized()):?>checked<? endif ?>
                        type="radio" 
                        name="ag-balls" 
                        class="ag-balls" 
                        value="0,1000000000" title="все баллы">
                    <div class="ag-shop-filter__variants-item">все баллы</div>
                  </label>
            </div>

            
            <div class="ag-shop-filter__confirm filter-passive">
              <button class="ag-shop-filter__confirm-button" type="submit" onclick="return ag_filter();">Подобрать</button>
            </div>
            <input type="hidden" id="ag-flag" value="all"/>
            <input type="hidden" id="ag-sorting" value="price-asc"/>
          </form>
          <!-- }}} Filter-->
