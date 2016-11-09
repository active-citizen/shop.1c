<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    // Получаем список банеров
    CModule::IncludeModule("iblock");
    $res = CIBlockElement::GetList(array("SORT"=>"ASC"),array(
        "ACTIVE"        =>  "Y",
        "IBLOCK_CODE"   =>  "baners_on_main",
        array(
            "LOGIC"=>"OR",
            "ACTIVE_FROM"   =>  "",
            "<ACTIVE_FROM"  =>  date("d.m.Y H:i:s")
        ),
        array(
            "LOGIC"=>"OR",
            "ACTIVE_TO"   =>  "",
            ">ACTIVE_TO"  =>  date("d.m.Y H:i:s")
        )
    ),false,false);
    
    
    $BANERS = array();
    while($baner = $res->getNext()){
        $BANERS[$baner["ID"]] = $baner;
        $BANERS[$baner["ID"]]["PROPERTIES"] = array();
        $res1 = CIBlockElement::GetProperty($baner["IBLOCK_ID"],$baner["ID"]);
        while($prop = $res1->getNext()){
            if($prop["PROPERTY_TYPE"]=='F')$prop["URL"] = CFile::GetPath($prop["VALUE"]);
            $BANERS[$baner["ID"]]["PROPERTIES"][$prop["CODE"]] = $prop;
            $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"] = array();
            if($prop["CODE"]=='BANER_CATALOG_LINK' && $prop["VALUE"]){
                $resCatalogLinkItem = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"   =>  "clothes",
                        "ID"            =>  $prop["VALUE"]
                    ),false,array("nTopCount"=>1),array(
                        "ID",
                        "PROPERTY_MINIMUM_PRICE",
                        "PROPERTY_RATING",
                        "PROPERTY_NEWPRODUCT",
                        "PROPERTY_SALELEADER",
                        "PROPERTY_SPECIALOFFER",
                        "PREVIEW_TEXT",
                        "PREVIEW_PICTURE",
                        "IBLOCK_SECTION_ID",
                        "NAME",
                        "DETAIL_PAGE_URL"
                    )
                );
                $arCatalogLinkItem = $resCatalogLinkItem->GetNext();

                // ID продукт
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["ID"] = 
                    $arCatalogLinkItem["ID"];
                // Вычисляем цену
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["PRICE"] = 
                    $arCatalogLinkItem["PROPERTY_MINIMUM_PRICE_VALUE"];
                // Вычисляем ссылку
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["URL"] = 
                    $arCatalogLinkItem["DETAIL_PAGE_URL"];
                // Вычисляем рейтинг
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["RATING"] = 
                    round($arCatalogLinkItem["PROPERTY_RATING_VALUE"]*5,2);
                // Вычисляем ИМЯ
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["NAME"] = 
                    $arCatalogLinkItem["NAME"];
                // Вычисляем новинку
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["NEWPRODUCT"] = 
                    $arCatalogLinkItem["PROPERTY_NEWPRODUCT_VALUE"];
                // Вычисляем хит продаж
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["SALELEADER"] = 
                    $arCatalogLinkItem["PROPERTY_SALELEADER_VALUE"];
                // Вычисляем хит спецпредложение
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["SPECIALOFFER"] = 
                    $arCatalogLinkItem["PROPERTY_SPECIALOFFER_VALUE"];
                // Вычисляем описание
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["PREVIEW_TEXT"] = 
                    $arCatalogLinkItem["PREVIEW_TEXT"];
                // Вычисляем адрес картинки
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["PREVIEW_PICTURE"] = 
                    CFile::GetPath($arCatalogLinkItem["PREVIEW_PICTURE"]);
                    
                // Вычисляем хотелки
                $resWishItem = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"           =>  "whishes",
                        "PROPERTY_WISH_PRODUCT" =>  $prop["VALUE"]
                    ),
                    false,array(),array("ID")
                );
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["WISHES"] = 
                    $resWishItem->SelectedRowsCount();
                // Вычисляем моя ли это хотелка
                $resWishItem = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"           =>  "whishes",
                        "PROPERTY_WISH_PRODUCT" =>  $prop["VALUE"],
                        "PROPERTY_WISH_USER"    =>  $USER->GetID(),
                    ),
                    false,array(),array("ID")
                );
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["MY_WISH"] = 
                    $resWishItem->GetNext()?1:0;
                    
                // Вычисляем раздел
                $resCatalogSection = CIBlockSection::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"   =>  "clothes",
                        "ID"=>$arCatalogLinkItem["IBLOCK_SECTION_ID"]
                    ),
                    false,
                    array("nTopCount"=>1),
                    array("NAME")
                );
                $arCatalogSection = $resCatalogSection->GetNext();
                $BANERS[$baner["ID"]]["CATALOG_LINK_DATA"]["SECTION_NAME"] = 
                    $arCatalogSection["NAME"];
            }
        }
    }

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


          <!-- Slider {{{-->
          <div class="ag-shop-slider">
            <div class="js-content-slider">
                
            <?foreach($BANERS as $BANER):?>
                <?if($BANER["CATALOG_LINK_DATA"]):?>
                <div class="ag-shop-slider__item">
                    <button class="ag-shop-slider-card__likes" type="button">
                      <div class="ag-shop-slider-card__likes-icon<? if($BANER["CATALOG_LINK_DATA"]["MY_WISH"]){?> wish-on<? }else{?> wish-off<? }?>"
                        productid="<?= $BANER["CATALOG_LINK_DATA"]["ID"]?>" 
                        onclick="return mywish(this);"></div>
                      <div class="ag-shop-slider-card__likes-count"
                      id="wishid<?= $BANER["CATALOG_LINK_DATA"]["ID"]?>"><?= 
                        $BANER["CATALOG_LINK_DATA"]["WISHES"]
                      ?></div>
                    </button>
                  <a class="ag-shop-slider-card" href="<?= $BANER["CATALOG_LINK_DATA"]["URL"]?>">
                        <img class="ag-shop-slider-card__image" src="<?= $BANER["CATALOG_LINK_DATA"]["PREVIEW_PICTURE"]?>">
                  <div class="ag-shop-slider-card__badges">
                      <? if($BANER["CATALOG_LINK_DATA"]["NEWPRODUCT"]):?>
                      <img class="ag-shop-slider-card__badge" src="/local/assets/images/badge__new.png">
                      <? endif ?>
                      <? if($BANER["CATALOG_LINK_DATA"]["SALELEADER"]):?>
                      <img class="ag-shop-slider-card__badge" src="/local/assets/images/badge__hit.png">
                      <? endif ?>
                      <? if($BANER["CATALOG_LINK_DATA"]["SPECIALOFFER"]):?>
                      <img class="ag-shop-slider-card__badge" src="/local/assets/images/badge__sale.png">
                      <? endif ?>
                  </div>
                  <div class="ag-shop-slider-card__info-layer">
                    <div class="ag-shop-slider-card__points">
                      <div class="ag-shop-slider-card__points-count">
                          <?= $BANER["CATALOG_LINK_DATA"]["PRICE"] ?>
                      </div>
                      <div class="ag-shop-slider-card__points-text"><? 
                        echo get_points($BANER["CATALOG_LINK_DATA"]["PRICE"]);
                      ?></div>
                    </div>
                    <div class="ag-shop-slider-card__info">
                      <p class="ag-shop-slider-card__category"><?= $BANER["CATALOG_LINK_DATA"]["SECTION_NAME"]?></p>
                      <p class="ag-shop-slider-card__description"><?= $BANER["CATALOG_LINK_DATA"]["PREVIEW_TEXT"]?></p>
                      <div class="ag-shop-slider-card__rating" title="Средняя оценка  <?= $BANER["CATALOG_LINK_DATA"]["RATING"]?>">
                        <? for($i=0;$i<round($BANER["CATALOG_LINK_DATA"]["RATING"]);$i++):?>
                        <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                        <? endfor ?>
                        <? for($j=0;$j<5-round($BANER["CATALOG_LINK_DATA"]["RATING"]);$j++):?>
                        <div class="ag-shop-slider-card__rating-item"></div>
                        <? endfor ?>
                      </div>
                      <h3 class="ag-shop-slider-card__name"><?= $BANER["CATALOG_LINK_DATA"]["NAME"] ?></h3>
                    </div>
                  </div>
                  </a>
                </div>
                <? endif ?>  
                  
                <?if(!$BANER["CATALOG_LINK_DATA"]):?>
                <div class="ag-shop-slider__item">
                  <a class="ag-shop-slider-card-dark" href="<?= $BANER["PROPERTIES"]["BANER_URL"]["VALUE"]?>">
                    <img class="ag-shop-slider-card-dark__image" src="" style="display:none;">
                  <div class="ag-shop-slider-card-dark__info-layer" style="background-image: url('<?= $BANER["PROPERTIES"]["BANER_PICTURE"]["URL"] ?>');">
                    <div class="ag-shop-slider-card-dark__info">
                      <h3 class="ag-shop-slider-card-dark__name"><?= $BANER["NAME"]?></h3>
                    </div>
                  </div></a></div>
                <? endif ?>  
            <? endforeach?>
            </div>
            <div class="ag-shop-slider__buttons">
              <div class="ag-shop-slider__prev"></div>
              <div class="ag-shop-slider__next"></div>
            </div>
            <div class="ag-shop-slider__dots"></div>
          </div>
          <!-- }}} Slider-->



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
                типы 
                <span class="ag-shop-filter__trigger" rel="types-filter" alltitle="все">
                  все
                </span>
              </div>
              <div class="ag-shop-filter__filters-item">
                у меня 
                <span class="ag-shop-filter__trigger" rel="balls-filter">
                  <?= $myBalls ?> 
                </span>
              </div>
            </div>
            
            <div class="ag-shop-filter__variants filter-active" id="wish-filter">
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

            <div class="ag-shop-filter__variants" id="types-filter">
              <? foreach($TYPES as $TYPE_ID=>$TYPE):?>
              <label>
                <input type="checkbox" class="ag-types" value="<?= $TYPE_ID ?>" title="<?= $TYPE["VALUE"]?>">
                <div class="ag-shop-filter__variants-item"><?= $TYPE["VALUE"]?></div>
              </label>
              <? endforeach ?>
            </div>

            <div class="ag-shop-filter__variants filter-passive" id="balls-filter" style="height: 42px;">
                <input type="text" id="ag-minPrice" value="0"/>
                <div id="slider"></div>
                <input type="text" id="ag-maxPrice" value="<?= str_replace(" ","",$MY_BALLS);?>"/>
            </div>

            
            <div class="ag-shop-filter__confirm filter-passive">
              <button class="ag-shop-filter__confirm-button" type="submit" onclick="return ag_filter();">Подобрать</button>
            </div>
            <input type="hidden" id="ag-flag" value="all"/>
            <input type="hidden" id="ag-sorting" value="price-asc"/>
          </form>
          <!-- }}} Filter-->
            <script>
                var vals = new Array();
                $("#slider").slider({
                    min: 0,
                    max: <?= str_replace(" ","",$MY_BALLS);?>,
                    values: [0,<?= str_replace(" ","",$MY_BALLS);?>],
                    range: true,
                    
                    stop: function(event, ui) {return true;},
                    slide: function(event, ui){
                        $('#ag-minPrice').val(ui.values[0]);
                        $('#ag-maxPrice').val(ui.values[1]);
                        return true;
                    }
                });
            </script>
          <!-- Catalog {{{-->
          <div class="ag-shop-catalog">
            <a name="products"><h1></h1></a>
            <!-- Для сортировки/фильтра-->
            <div class="ag-shop-catalog__filter">
              <div class="ag-shop-catalog__filter-instance">
                <div class="ag-shop-catalog__filter-item"><a rel="all" class="ag-shop-menu__link ag-shop-menu__link_flag" href="#">Все товары</a></div>
                <div class="ag-shop-catalog__filter-item"><a rel="actions" class="ag-shop-menu__link ag-shop-menu__link_flag" href="#">Акции</a></div>
                <div class="ag-shop-catalog__filter-item"><a rel="news" class="ag-shop-menu__link ag-shop-menu__link_flag" href="#">Новые поступления</a></div>
                <div class="ag-shop-catalog__filter-item"><a rel="populars" class="ag-shop-menu__link ag-shop-menu__link_flag" href="#">Популярные</a></div>
              </div>
              <div class="ag-shop-catalog__filter-instance">Сначала:
                <div class="ag-shop-catalog__filter-item"><a rel="price-asc" class="ag-shop-menu__link ag-shop-menu__link_sorting" href="#">Дешевые</a></div>
                <div class="ag-shop-catalog__filter-item"><a rel="price-desc" class="ag-shop-menu__link ag-shop-menu__link_sorting" href="#">Дорогие</a></div>
                <div class="ag-shop-catalog__filter-item"><a rel="rating-desc" class="ag-shop-menu__link ag-shop-menu__link_sorting" href="#">Популярные</a></div>
              </div>
            </div>
            <div class="ag-shop-catalog__items-container">
              <div class="grid grid--bleed grid--justify-center catalog-ajax-block">
              </div>
            </div>
    
          </div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
