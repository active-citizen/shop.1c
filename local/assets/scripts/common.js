$(document).ready(function() {

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

    /**
     * переключение предпросмотра картинок
     */
    $('.ag-shop-card__preview').click(function(){
        $(this).parent().find('.ag-shop-card__preview').removeClass('ag-shop-card__preview--active');
        $(this).addClass('ag-shop-card__preview--active');
        $('.ag-shop-card__image-container').css('background-image','url('+$(this).attr('rel')+')');
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
