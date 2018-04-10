
/**
    Сортировка плитки
*/
function teaserSorting(sortString){
    $('#sorting').val(sortString);
    applyFilter();
    return false;
}

/**
    Применение фильтра без 
*/
function applyFilter(){
    // Определяем базовую строку запроса
    var query = '/catalog/index.ajax.php?form=filter';
    // Добавляем поля из формы
    $('#desktopCatalogFilterForm input').each(function(){
        if($(this).attr("type")=='checkbox' && $(this).is(':checked'))
            query+="&"+$(this).attr("name")+'='+$(this).val();
        else if($(this).attr("type")=='checkbox')
            query+="";
        else
            query+="&"+$(this).attr("name")+'='+$(this).val();
    });

    var nPageNum = 1;
    $('.desktop-products-container').addClass('teaser-loading');
    // Отправляем запрос на получение 1-й страницы
    $.get(query,function(data){
        var search = query;
        var re=/^(.*)\/$/;
        // Узнаём раздел

        search = search.replace(/^.*(\?.*)$/,'$1');
        search = search.replace(/[&\?]page=\d+/,'');
        search = search.replace(re,"$1");

        if(search=='') 
            newsearch = search+'?page='+nPageNum+'/'
        else
            newsearch = search+'&page='+nPageNum+'/';
        console.log(newsearch);
        window.history.replaceState({}, search, newsearch);
        //document.location.hash = "PAGE-"+nPageNum;
        $('.more-button').remove();
        $('.desktop-products-container').html(data);
        $('.desktop-products-container').removeClass('teaser-loading');
        wishes_load();
    })
     
    return false;
}

$(document).ready(function(){
    $('.all-checked').click(function(){
        if(!$(this).parent().parent().find("input:checked").length){
            $(this).parent().parent().find("input").first().prop("checked",true);
        }
    });
});
