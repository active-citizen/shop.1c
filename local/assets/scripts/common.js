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
    
    /**
     * Количество заказанного
     */
    $('.ag-shop-card__count-button').click(function(){
        var count = parseInt($('.ag-shop-card__count-number').html());
        var price = parseInt($('.ag-shop-item-card__points-count').html());
        if(
            $(this).hasClass('ag-shop-card__count-button--add') 
            && ((count+1)*price)<accountSum
            && (count+1)<offerCounts
        ){
            count++;
        }
        else if($(this).hasClass('ag-shop-card__count-button--sub') && count>1){
            count--;
        }
        $('.ag-shop-card__count-number').html(count);
        $('#ag-shop-card__total-points').html(count*price);
        $('.ag-shop-card__submit-button strong').html(count*price);
    });
    
    /**
     * Выбор конкретного предложения
    */
    $('.ag-shop-card__sizes input').change(function(){
        
        var props = {};
        var offerProps = {};

        // Определяем набор
        $('.ag-shop-card__sizes').each(function(){
            props[$(this).find('input:checked').attr("name")] = $(this).find('input:checked').val();
        });
        
        var targetMatches = Object.keys(props).length
        // Определяем какому предложению соответствует набор
        for(offerId in arOffers){
            // Если число свойств предложения не совпадает с числом выбранных свойств
            if(Object.keys(arOffers[offerId]["1C_PROPS"]).length!=Object.keys(props).length)
                continue;
            // Составляем набор свойств предложения
            offerProps = {}
            for(prop in arOffers[offerId]["1C_PROPS"])
                offerProps[prop] = arOffers[offerId]["1C_PROPS"][prop].ID;
            
            // Сравниваем выбранный набор свойст с набором предложения
            targetMatches = Object.keys(props).length
            for(prop in props)
                if(arOffers[offerId]["1C_PROPS"][prop].ID==props[prop])
                    targetMatches--;
            // Если не все свойства совпали - значит НЕ нужное нам предложение
            if(targetMatches==0)break;
        }
        
        // Если выход по совпадению, значи предложение нашли
        if(!targetMatches){
            totalOfferId = offerId;
            arOffers[offerId].PRICE;
            $('.ag-shop-card__count-number').html(1);
            $('#ag-shop-card__total-points').html(parseInt(arOffers[offerId].PRICE));
            $('.ag-shop-card__submit-button strong').html(parseInt(arOffers[offerId].PRICE));
            
            $('.ag-shop-card__previews-container .ag-shop-card__preview').remove();
            for(i in arOffers[offerId].PICS){
                $('.ag-shop-card__previews-container').append('<div class="ag-shop-card__preview"></div>');
                $('.ag-shop-card__previews-container .ag-shop-card__preview').last().attr("rel",arOffers[offerId].PICS[i]);
                $('.ag-shop-card__previews-container .ag-shop-card__preview').last().attr("style","background-image: url("+arOffers[offerId].PICS[i]+");");
                $('.ag-shop-card__previews-container .ag-shop-card__preview').last().click(function(){
                    $(this).parent().find('.ag-shop-card__preview').removeClass('ag-shop-card__preview--active');
                    $(this).addClass('ag-shop-card__preview--active');
                    $('.ag-shop-card__image-container').css('background-image','url('+$(this).attr('rel')+')');
                });
            }
            $('.ag-shop-card__image-container').css('background-image','url('+arOffers[offerId].PICS[0]+')');
            $('.ag-shop-card__previews-container .ag-shop-card__preview').first().addClass('ag-shop-card__preview--active');
            
            $('.ag-shop-card__places').find('label').remove();
            var count=0;
            for(i in arOffers[offerId].STORAGES){
                $('.ag-shop-card__places').append('<label><input type="radio" name="place" value="'+i
                    +'" '+(count==0?'checked':'')+' ><div class="ag-shop-card__places-item">'+
                arStorages[i].TITLE+'</div></label>');
                if(!count)count = i;
            }
            selectStorage(arStorages[count].ID);
            
        }
        
        
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


function selectStorage(storageId){
    $('.ag-shop-card__selected-place-table').html('');
    var value= '';
    if(value = arStorages[storageId].ADDRESS)$('.ag-shop-card__selected-place-table').append(getStorageRow('Адрес',value));
    if(value = arStorages[storageId].PHONE)$('.ag-shop-card__selected-place-table').append(getStorageRow('Телефон',value));
    if(value = arStorages[storageId].SCHEDULE)$('.ag-shop-card__selected-place-table').append(getStorageRow('Режим',value));
    if(value = arStorages[storageId].EMAIL)$('.ag-shop-card__selected-place-table').append(getStorageRow('Сайт',value));
}


function getStorageRow(title,value){
    return '<tr><td>'+title+':</td><td>'+value+'</td></tr>'
}

