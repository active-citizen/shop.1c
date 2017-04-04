<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if(!$USER->IsAdmin())die;

$APPLICATION->SetTitle("Формирование карт");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$resManufacturers = CIblockElement::GetList(array(),array("IBLOCK_ID"=>MANUFACTURER_IB_ID));
$arManufacturers = array();
while($arManufacturer = $resManufacturers->GetNext()){
    $resProps = CIBlockElement::GetProperty(MANUFACTURER_IB_ID,$arManufacturer["ID"]);
    $arProps = array();
    while($arProp = $resProps->GetNext())$arProps[$arProp["CODE"]] = $arProp;
    $arManufacturer["PROPS"] = $arProps;
    $arManufacturers[] = $arManufacturer;
    
}



//echo "<pre>";
//print_r($arManufacturers);
//echo "</pre>";

?>
<h1>Формирование карт(интерфейс администратора)</h1>
<h2>Центры выдачи</h2>
<? $resStore = CCatalogStore::GetList();?>
<table class="manufacturers">
    <tr>
        <th width="50px">ID</th>
        <th width="300px">Имя</th>
        <th>Адрес</th>
        <th width="200px">Координаты</th>
        <th width="300px">Карта</th>
    </tr>
    <? while($arStore = $resStore->GetNext()):?>
    <tr>
        <td class="man-id"><?= $arStore["ID"];?></td>
        <td class="man-name"><?= $arStore["TITLE"];?></td>
        <td class="man-address"><?= $arStore["ADDRESS"];?></td>
        <td class="man-coords"></td>
        <td class="man-map"><img src=""></td>
        <td class="man-store">store</td>
    </tr>
    <? endwhile?>
</table>
<h2>Производители</h2>
<table class="manufacturers">
    <tr>
        <th width="50px">ID</th>
        <th width="300px">Имя</th>
        <th>Адрес</th>
        <th width="200px">Координаты</th>
        <th width="300px">Карта</th>
    </tr>
    <? foreach($arManufacturers as $k=>$arManufacturer):
        //if($k>0)break;
        $arManufacturer["PROPS"]["HOW_FIND"]["VALUE"] = html_entity_decode($arManufacturer["PROPS"]["HOW_FIND"]["VALUE"]);
        $arManufacturer["PROPS"]["HOW_FIND"]["VALUE"] = str_replace("\n","",$arManufacturer["PROPS"]["HOW_FIND"]["VALUE"]);
    ?>
    <tr <?
    if(!trim(html_entity_decode($arManufacturer["PROPS"]["ADDRESS"]["VALUE"]))){?> class="man-error";<?} ?>>
        <td class="man-id"><?= $arManufacturer["ID"];?></td>
        <td class="man-name"><?= $arManufacturer["NAME"];?></td>
        <td class="man-address"><?= $arManufacturer["PROPS"]["ADDRESS"]["VALUE"];?></td>
        <td class="man-coords"></td>
        <td class="man-map"><img src=""></td>
    </tr>
    <? endforeach ?>
</table>

<style>
table.manufacturers{
    width: 96%;
    margin: 2%;
    border-collapse: collapse;
}

    table.manufacturers th{
        background-color: #EFEFEF;
        border: 1px #AAA solid;
    }

    table.manufacturers td{
        border: 1px #AAA solid;
    }


td.man-id{
    text-align: right;
    padding: 4px;
}

.man-error{
    background-color: #FDD;
}
.man-warn{
    background-color: #DDF;
}


</style>

<script src="https://api-maps.yandex.ru/1.1/index.xml" type="text/javascript"></script>
<script>
    
    var row_obj = $('td.man-id').first().parent();
    
    
    
    $('td.man-id').each(function(){
        var row_obj = $(this).parent();
        var manid = row_obj.find('.man-id').first().html();
        var manStore = row_obj.find('.man-store').first().html()?1:0;
        var address = row_obj.find('.man-address').first().html();
        var name  = row_obj.find('.man-name').first().html();
        var coordsObj = row_obj.find('.man-coords').first();
        var mapImg = row_obj.find('.man-map img').first();
        var Lat = '';
        var Lng = '';
        var Coords = '';
        coordsObj.html('...обработка');
        
        YMaps.jQuery(function () {
            // Создает экземпляр карты и привязывает его к созданному контейнеру
            var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);
            var geocoder = new YMaps.Geocoder(address);
            YMaps.Events.observe(geocoder, geocoder.Events.Load, function () {
                if (this.length()) {
                    Lat = this.get(0).getGeoPoint().getLat(); //  55,57
                    Lng = this.get(0).getGeoPoint().getLng(); //  36,39
                    zoom = 16;
                    // Тест на подозрительность
                    if(Lat<55 || Lat>57 || Lng<36 || Lng>39
                    ){
                        zoom = 5;
                        row_obj.addClass('man-warn');
                    }
                    Coords = Lng+','+Lat;
                    coordsObj.html(Coords);
                    
                    url = '/servitor/maps/save.ajax.php?id='+manid+'&coords='+Coords+'&zoom='+zoom+'&store='+manStore;
                    
                    $.ajax({
                        async: false,
                        url:url,
                        success: function(data){
                            var answer = JSON.parse(data);
                            mapImg.attr('src',answer.url)
                        }
                    });
                    
                    //url = 'http://static-maps.yandex.ru/1.x/?lang=ru-RU&ll='+Coords+
                    //    '&z='+zoom+'&l=map,skl&size=300,300&pt='+Coords+',flag';
                    //mapImg.attr('src',url);
                    
                }else {
                    coordsObj.html("Ничего не найдено")
                }
            });
             
            YMaps.Events.observe(geocoder, geocoder.Events.Fault, function (error) {
                coordsObj.html("Произошла ошибка: " + error.message)
            });
        });
    });
    
        
</script>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
