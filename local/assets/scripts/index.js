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

    if(
        $(this).hasClass('ag-shop-filter__trigger--active')
        && $('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
        $('#'+$(this).attr("rel")).removeClass('filter-active')
    }else if(
        $(this).hasClass('ag-shop-filter__trigger--active')
        && !$('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
        $('.ag-shop-filter__variants').removeClass('filter-active');
        $('#'+$(this).attr("rel")).addClass('filter-active');
    }
    else if(
        !$(this).hasClass('ag-shop-filter__trigger--active')
        && $('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
    }
    else if(
        !$(this).hasClass('ag-shop-filter__trigger--active')
        && !$('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
        $('.ag-shop-filter__variants').removeClass('filter-active');
        $('#'+$(this).attr("rel")).addClass('filter-active');
    }


    $('.ag-shop-filter__variants').find(":before").css("left","200");

    $('.ag-shop-filter__trigger').removeClass('ag-shop-filter__trigger--active');
    
    $(this).addClass('ag-shop-filter__trigger--active');
    
    
  });
  
  
  /**
   * Выбор элементов фильтра
   */
  $('.ag-shop-filter__variants input').click(function(){
    if($(this).parent().parent().find('input:checked').length>3){
       $(this).removeAttr('checked');
        alert('Нельзя выбирать более 3 элементов');
        return false;
    }
    return true;
  });


  $('.ag-shop-filter__variants input').change(function(){
    if($(this).parent().parent().find('input:checked').length>3){
        $(this).removeAttr('checked');
        alert('Нельзя выбирать более 3 элементов');
        return false;
    }
    input_variant_click($(this));
    
    ag_filter();
    return false;
  });   

    // Клик на фильтрацию и сортировку
    $('.ag-shop-menu__link_flag').click(function(){
        $('.ag-shop-menu__link_flag').removeClass('ag-shop-menu__link--active');
        $(this).addClass('ag-shop-menu__link--active');
        $('#ag-flag').val($(this).attr('rel'));
        ag_filter();
        return false;
    });
    
    $('.ag-shop-menu__link_sorting').click(function(){
        $('.ag-shop-menu__link_sorting').removeClass('ag-shop-menu__link--active');
        $(this).addClass('ag-shop-menu__link--active');
        $('#ag-sorting').val($(this).attr('rel'));
        ag_filter();
        return false;
    });

});


function input_variant_click(obj){
    var triggerTab = $('span[rel="'+obj.parent().parent().attr("id")+'"]');
    var fieldTab = obj.parent().parent();
    var arrOptions = Array();
    
    triggerTab.html('');
    fieldTab.find('input').each(function(){
        if($(this).is(':checked')){
            arrOptions.push($(this).attr("title"));
        }
    });
    triggerTab.html(arrOptions.join(", "));
    if(!triggerTab.html())triggerTab.html(triggerTab.attr('alltitle'));

    return true;
}

