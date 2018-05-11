
/**
    Сортировка плитки
*/
function teaserSorting(sortString){
    $('#sorting').val(sortString);
    applyFilter();
    return false;
}

/**
    Рвзмер плитки
*/
function teaserSize(size){
    $('#smallicons').val(size);
    if(parseInt(size)==0){
        $('.desktop-products-container').removeClass('desktop-products-container--gridSmall');
        $('.desktop-products-container').removeClass('desktop-products-container--gridList');
    }
    else if(parseInt(size)==1){
        $('.desktop-products-container').removeClass('desktop-products-container--gridSmall');
        $('.desktop-products-container').removeClass('desktop-products-container--gridList');
        $('.desktop-products-container').addClass('desktop-products-container--gridSmall');
    }
    else if(parseInt(size)==2){
        $('.desktop-products-container').removeClass('desktop-products-container--gridSmall');
        $('.desktop-products-container').addClass('desktop-products-container--gridList');
    }
    applyFilter();
    return false;
}



$(document).ready(function(){
    $('.all-checked').click(function(){
        if(!$(this).parent().parent().find("input:checked").length){
            $(this).parent().parent().find("input").first().prop("checked",true);
        }
    });

    $('#showProductsAll').click(function(){
        $('#not_exists').val($(this).prop('checked')?0:1);
    });

    $('#desktopProductsFilterReset').click(function(){
        $('#not_exists').val($('#showProductsAll').prop('checked')?0:1);
        applyFilter();
    });
});
