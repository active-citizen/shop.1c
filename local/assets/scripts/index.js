$(document).ready(function() {

  var animationDelay = 250;

  // list toggle
  var menuButton = $('.js-menu__button');
  var menuList = $('.js-menu__list');

  menuButton.on('click', function(e) {
      e.preventDefault();
      if ( menuList.is(':visible') ) {
          menuList.slideUp(animationDelay);
          menuButton.removeClass('ag-shop-menu__button--close');
      } else {
          menuList.slideDown(animationDelay);
          menuButton.addClass('ag-shop-menu__button--close');
      }
  });

  /**********/

  // init slider
  $('.js-content-slider').slick({
    mobileFirst: true,
    centerPadding: 0,
    arrows: false,
    dots: true,
    dotsClass: 'ag-shop-slider__dots',
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    centerMode: false,
    variableWidth: true,
    autoplay: false,
    responsive: [{
        breakpoint: 992,
        settings: {
            arrows: true
        }
    }]
  });
  
  /**
   * Переключение вкладок
   */
  $('.ag-shop-filter__trigger').click(function(){
    $('.ag-shop-filter__trigger').removeClass('ag-shop-filter__trigger--active');
    $(this).addClass('ag-shop-filter__trigger--active');
    $('.ag-shop-filter__variants').removeClass('filter-active');
    $('#'+$(this).attr("rel")).addClass('filter-active');
  });
  
  /**
   * Выбор элементов фильтра
   */
  $('.ag-shop-filter__variants-item').click(function(){
    if($(this).hasClass('ag-shop-filter__trigger--active')){
      $(this).parent().find('input').
    }
    else{
    }

  });   

});
