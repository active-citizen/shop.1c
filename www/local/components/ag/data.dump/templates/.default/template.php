<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? 
    $arIBlocks = array(
        "catalog:clothes"       =>  array(
            "NAME"      =>  "Товары",
        ),
        "catalog:manuacturers"  =>    array(
            "NAME"      =>  "Производители",
        ),
        "offers:clothes_offers" =>    array(
            "NAME"      =>  "Торговые предложения",
        ),
        "banners:baners_on_main" =>    array(
            "NAME"      =>  "Банеры на главной",
        ),
        "references:whishes" =>    array(
            "NAME"      =>  "Мои желания",
        ),
        "references:marks" =>    array(
            "NAME"      =>  "Мои оценки"
        ),
        "content:content_articles" =>    array(
            "NAME"      =>  "Статьи"
        ),
        "content:content_faq" =>    array(
            "NAME"      =>  "FAQ"
        ),
    );
?>
<ul class="nav nav-pills nav-stacked partners-order-menu">
    <li class="active">
        <a href="#" rel="data-export">
            Экспорт данных
        </a>
    </li>
    <li>
        <a href="#" rel="data-import">
            Импорт данных
        </a>
    </li>
</ul>
<div class="partners-order-main" id="data-export">
    <h3>Инфоблоки</h3>
    <table class="table">
        <? $count=0;foreach($arIBlocks as $sIBlockName=>$arIBlock):?>
        <? $count++;?>
        <tr>
            <td style="width: 32px;">
                <input type="checkbox" class="form-control iblock-checkbox"
                rel="<?= $sIBlockName?>" num="<?= $count?>">
            </td>
            <td style="width: 200px;">
                <?= $arIBlock["NAME"]?>
            </td>
            <td>
                <div class="progress" num="<?= $count?>">
                  <div class="progress-bar" role="progressbar"
                  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                  style="width: 0%;">
                      0%
                  </div>
                </div>
            </td>
        </tr>
        <? endforeach ?>
        <tr>
            <td colspan="3">
                <input type="submit" class="btn btn-primary" 
                style="float:right;" id="iblock-start" value="Сформировать дамп"/>
                <input type="submit" class="btn btn-danger" 
                style="float:right;" id="iblock-stop" value="Остановить"
                disabled="true"/>
            </tr>
        </tr>
    </table>
    <h3>Складские остатки</h3>

    <h3>Пользователи</h3>

    <h3>Заказы</h3>

</div>
<div class="partners-order-main" id="data-import" style="display:none;">
    <h3>Импорт данных</h3>
</div>


<script src="/local/assets/scripts/partners.js"></script>

<style>
.form-control{
    height: 16px;
}
.btn{
    margin: 10px;
}
</style>

<script>
var iblock_processed = new Array();

$('#iblock-stop').click(function(){
    $(this).prop('disabled',true);
    $('#iblock-start').prop("disabled",false);
});

$('#iblock-start').click(function(){
    $('.iblock-checkbox').each(function(){
        iblock_processed.push({
            "iblock":   $(this).attr('rel'),
            "num":      $(this).attr('num'),
            "offset":   0,
            "state":    parseInt($(this).attr('num'))>1?'not runned':'runned'
        });
    });
    this.disabled = true;
    $('#iblock-stop').prop('disabled',false);
    iblock_step();
 });

function iblock_step(){
    var runned_iblock = {};
    for(i in iblock_processed){
        if(iblock_processed[i].state=='runned'){
            runned_iblock = iblock_processed[i];
            break;
        }
    }
    $.get(
        "/local/components/ag/data.dump/iblocks_dump.ajax.php?"
        +"name="+runned_iblock.iblock
        +"&offset="+runned_iblock.offset
        ,
        function(){
            
//          setTimeout(function(){iblock_step();},100);
        }
            
    );
}

</script>
