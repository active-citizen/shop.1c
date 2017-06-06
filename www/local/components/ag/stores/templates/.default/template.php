<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>


            <script src="https://api-maps.yandex.ru/1.1/index.xml" type="text/javascript"></script>

            <div class="ag-shop-rules__container">
                
            <? foreach($arResult["stores"] as $arStore):?>
              <div class="ag-shop-content__limited-container js-spoiler__link" id="store-click-<?= $arStore["ID"];?>">
                <a 
                    class="ag-shop-rules__spoiler-link hash-navigation" 
                    href="#<?= $arStore["ID"];?>"
                    name="<?= $arStore["ID"];?>"
                >- <?= $arStore["TITLE"]?>, <span>
                    <?= $arStore["ADDRESS"]?> 
                    </span>
                </a>
              </div>
              <div class="ag-shop-rules__address js-spoiler__content">
                <div class="ag-shop-content__limited-container">
                  <div class="grid grid--bleed">
                    <div class="grid__col-shrink">
                      <div class="ag-shop-rules__address-container" id="YMapsID<?= $arStore["ID"] ?>" style="width:420px;height:150px"></div>
                      <script>
                      <?
                        foreach($arStore as $k=>$v)
                            $arStore[$k] = str_replace("\r"," ",$v);
                        foreach($arStore as $k=>$v)
                            $arStore[$k] = str_replace("\n"," ",$v);
                        foreach($arStore as $k=>$v)
                            $arStore[$k] = str_replace("\t"," ",$v);
                      ?>
                        YMaps.jQuery(function () {
                            // Создает экземпляр карты и привязывает его к созданному контейнеру
                            var map = new YMaps.Map(YMaps.jQuery("#YMapsID<?= $arStore["ID"] ?>")[0]);
                            var geocoder = new YMaps.Geocoder("<?=
                            htmlspecialchars($arStore["ADDRESS"])?>");
                            map.setCenter(new YMaps.GeoPoint(37.64, 55.76), 10);
                            YMaps.Events.observe(geocoder, geocoder.Events.Load, function () {
                                if (this.length()) {
                            
                                    map.setCenter(new YMaps.GeoPoint(
                                        this.get(0).getGeoPoint().getLng(),
                                        this.get(0).getGeoPoint().getLat()
                                    ), 16);
                                    
                                    var placemark = new YMaps.Placemark(new YMaps.GeoPoint(
                                        this.get(0).getGeoPoint().getLng(),
                                        this.get(0).getGeoPoint().getLat()
                                    ));
                                    placemark.name = '<?=
                                    htmlspecialchars($arStore["TITLE"])?>';
                                    placemark.description = '<?=
                                    htmlspecialchars($arStore["SCHEDULE"])?>.<br/> Тел: <?=
                                    htmlspecialchars($arStore["PHONE"])?>';
                                    placemark.hideIcon = true;
                                    
                                    map.addOverlay(placemark);
                                    map.panTo(placemark)
                                }else {
                                    console.log("Ничего не найдено")
                                }
                            });
                             
                            YMaps.Events.observe(geocoder, geocoder.Events.Fault, function (error) {
                                console.log("Произошла ошибка: " + error.message)
                            });                        
                        })
                      </script>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-rules__address-container">
                        <table class="ag-shop-rules__address-info">
                          <tr>
                            <td>Адрес:</td>
                            <td><?= $arStore["ADDRESS"]?></td>
                          </tr>
                          <tr>
                            <td>Телефон:</td>
                            <td><?= $arStore["PHONE"]?></td>
                          </tr>
                          <tr>
                            <td>Режим:</td>
                            <td><?= $arStore["SCHEDULE"]?></td>
                          </tr>
                          <tr>
                            <td>Сайт:</td>
                            <td><a href="<?= $arStore["EMAIL"]?>" target="_blank"><?= 
                                mb_strlen($arStore["EMAIL"])<=32
                                ?
                                $arStore["EMAIL"]
                                :
                                mb_substr($arStore["EMAIL"],0,32)."..."
                            ?></a></td>
                          </tr>
                        </table>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-rules__address-container">
                        <div class="ag-shop-rules__address-description">
                          <div class="ag-shop-rules__address-text"><?= $arStore["DESCRIPTION"]?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <? endforeach?>
            </div>
            
