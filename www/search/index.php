<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");
//require($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CSearch/CSearch.class.php");
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CSection/CSection.class.php");
use AGShop\Section as Section;

$objSections = new \Section\CSection;
$arSections = $objSections->get();



?>
<div class="ag-shop__main">
    <div class="ag-shop-content">
        <form class="search-form" onsubmit="return onSearch();">
            <select name="options[SECTION_ID]" class="section_id">
                <option value="0">-Все-</option>
                <? foreach($arSections as $arSection):?>
                <option value="<?= $arSection["ID"]?>">
                    <?= $arSection["NAME"]?>
                </option>
                <? endforeach ?>
            </select>
            <input type="hidden" name="options[LIMIT]" value="12">
            <input type="hidden" name="options[PAGE]" value="1">
            <input type="text" name="query" placeholder="Поиск" class="query">
            <input type="submit" name="go" value="Искать" class="submit">
        </form>
        <div class="search-results grid grid--bleed grid--justify-center">
        </div>
    </div>
</div>

<script>
    
    
    $(document).ready(function(){
        $('.query').autocomplete({source:"/search/phrases.ajax.php",minLength:2});
    })
    
    function onSearch(){
        var url = "/search/results.ajax.php?";
        var queryString = "query="+$('.search-form .query').val();
        queryString +="&options[FILTER][SECTION_ID]="+$('.search-form .section_id').val();
        queryString +="&options[PAGE]="+$('.search-form input[name="options[PAGE]"]').val();
        queryString +="&options[LIMIT]="+$('.search-form input[name="options[LIMIT]"]').val();
        $('.search-results').load(
            url+encodeURI(queryString)
        );
        return false;
    }
    
    function moreSearches(obj){
        var moreLink = $(obj)
        $.get(
            '/search/results.ajax.php?'+$('.catalog-page-input').val(),
            function(data){
                $('.catalog-page-input').remove();
                moreLink.parent().parent().remove();
                $('.search-results').append(data);
            }
        );
        return false;
    }
</script>

<style>
.ag-shop-item-card__name{
    color: black;
}

    .search-form {
        text-align: center;
    }


@media(max-width: 459px){

    .search-form {
        padding: 32px 3%;
        width: 90%;
        text-align: center;
    }
    
    .search-form select{
        width: 30%;
    }

    .search-form .query{
        width: 45%;
    }

    .search-form .submit{
        width: 15%;
    }    
    
}

</style>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
