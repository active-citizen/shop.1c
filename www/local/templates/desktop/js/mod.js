
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
        $('.btn-catalog-header__icon--filter .mobile-header-filter-btn__icon-active').remove();
        applyFilter();
    });

    $('#desktopProductsFilterSubmit').click(function(){
        if(!$('.btn-catalog-header__icon--filter .mobile-header-filter-btn__icon-active').length)
            $('.btn-catalog-header__icon--filter').append('<span class="mobile-header-filter-btn__icon-active"></span>');
    });
});


if(typeof(getdocWidth)!=='function'){
  function getdocWidth(){
    /* Получаем строку из юзерагента браузера */
    var ua = navigator.userAgent.toLowerCase();

    /* Проверяем, если в строке есть "safari",
       то скорее всего это webkit, поэтому заходим в этот if
    */
    if (ua.indexOf('safari') != -1) {

      /* Если это браузер на основе Chrome, то записываем в
         переменную docWidth значение window.innerWidth */
      if (ua.indexOf('chrome') > -1) {
        docWidth = window.innerWidth;

      /* Если это не Chrome, то значит это Safari,
         поэтому в переменной docWidth уже сохраняем значение document.documentElement.clientWidth*/
      } else {
        docWidth = document.documentElement.clientWidth;
      }
    /* Если в строке юзерагента нет "Safari", значит это какой-то иной браузер,
       поэтому отдаём ему window.innerWidth
       */
    }else{
     docWidth = window.innerWidth;
    }
    /* Ну и возвращаем переменную */
  return docWidth;
  };
}
  // Truncate - Shave

if(typeof(truncTitle)!==false){
   function truncTitle() {

    var getCurrentWindowWidth = getdocWidth();
    var getCurrentGridStatus = document.querySelector('.desktop-products-container');
    if ( getCurrentWindowWidth < 1024 && $(getCurrentGridStatus).hasClass("desktop-products-container--gridList")) {
      $('.desktop-product-title__name').shave(80);
    }
    else {
      $('.desktop-product-title__name').shave(40);
    }

    $('.desktop-product-details-description').shave(18);
    $('.desktop-product-details-warning').shave(18);
  };
}



