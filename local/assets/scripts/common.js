$(document).ready(function() {

    if(document.location.hash){
        var id = document.location.hash.split("#")[1];
        var re = /&/;
        if(id && !re.test(id))
            $('#store-click-'+id).trigger('click');
        if(id && !re.test(id))
            $('#faq-click-'+id).trigger('click');
    }

    $('.hash-navigation').click(function(){
        document.location.hash = $(this).attr("href").split("#")[1];
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
        
        var avaible = parseInt(arOffers[totalOfferId]['STORAGES'][$('input[name="place"]:checked').val()]);
        
        var count = parseInt($('.ag-shop-card__count-number').html());
        var price = parseInt($('.ag-shop-item-card__points-count').html());
        if(
            $(this).hasClass('ag-shop-card__count-button--add') 
            && ((count+1)*price)<accountSum
            && count<avaible
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
                $('.ag-shop-card__places').append('<label><input onclick="return selectStorage('+arStorages[i].ID+
                ');" type="radio" name="place" value="'+i
                    +'" '+(count==0?'checked':'')+' ><div class="ag-shop-card__places-item">'+
                arStorages[i].TITLE+'</div></label>');
                if(!count)count = i;
            }
            selectStorage(arStorages[count].ID);
            
        }
    });

    loadComments();

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
    totalStoreId = storageId;
    $('.ag-shop-card__selected-place-table').html('');
    var value= '';
    if(value = arStorages[storageId].ADDRESS)$('.ag-shop-card__selected-place-table').append(getStorageRow('Адрес',value));
    if(value = arStorages[storageId].PHONE)$('.ag-shop-card__selected-place-table').append(getStorageRow('Телефон',value));
    if(value = arStorages[storageId].SCHEDULE)$('.ag-shop-card__selected-place-table').append(getStorageRow('Режим',value));
    if(value = arStorages[storageId].EMAIL)
        $('.ag-shop-card__selected-place-table').append(getStorageRow('Сайт','<a href="'+value+'" target="_blank">'+value+'</a>'));
    $('.ag-shop-card__selected-place-station').html(arStorages[storageId].TITLE);
    $('.ag-shop-card__remaining-count .ag-shop-card__remaining-count-text').css('display','none');
    $('.ag-shop-card__remaining-count .ag-shop-card__remaining-count-text').each(function(){
        if(
            arOffers[totalOfferId]['STORAGES'][storageId]>=parseInt($(this).attr('fromAmmount'))  
            &&
            arOffers[totalOfferId]['STORAGES'][storageId]<=parseInt($(this).attr('toAmmount')) 
        ){
            $(this).css('display','inline-block');
            // Если на складе меньше, чем уже набрали
            if(arOffers[totalOfferId]['STORAGES'][storageId]<parseInt($('.ag-shop-card__count-number').html())){
                $('.ag-shop-card__count-number').html(arOffers[totalOfferId]['STORAGES'][storageId]);
                var count = parseInt($('.ag-shop-card__count-number').html());
                var price = parseInt($('.ag-shop-item-card__points-count').html());
                $('.ag-shop-card__count-number').html(count);
                $('#ag-shop-card__total-points').html(count*price);
                $('.ag-shop-card__submit-button strong').html(count*price);
            }
        }
    });
    
    loadComments();
}


function getStorageRow(title,value){
    return '<tr><td>'+title+':</td><td>'+value+'</td></tr>'
}


function addComment(){
    var comment = $('.ag-shop-card__review-form-input').val();
    var mark = $('.ag-shop-card__review-form-container .ag-shop-card__rating').attr("rel");
    var productId = $('.ag-shop-card__review-form').attr("prodictid");
    if(!mark){
        alert('Вы не поставили оценку');
        return false;
    }
    if(!productId){
        alert('Не указан ID продукта');
        return false;
    }
    if(comment.length<3){
        alert('Вы не написали текст комментария');
        return false;
    }
    $.post(
        "/local/components/ag/card/addcomment.ajax.php",
        {
            "comment":comment,
            "mark":mark,
            "productid":productId
        },
        function(data){
            try{
                var answer = JSON.parse(data);
            }   
            catch(e){
                alert(data);
            }
        
            if(answer.error && !answer.success){
                alert(answer.error);
                return false;
            }
            $('.ag-shop-card__review-form').fadeOut();
            loadComments();
        }
    );
    
    
    return false;
}

function setMark(obj){
    $('.ag-shop-card__review-form-container .ag-shop-card__rating').attr("rel",parseInt($(obj).attr('rel')));
    $('.ag-shop-card__review-form-container .ag-shop-card__rating .ag-shop-item-card__rating-item').removeClass('ag-shop-slider-card__rating-item--active');
    $('.ag-shop-card__review-form-container .ag-shop-card__rating .ag-shop-item-card__rating-item').each(function(){
        if(parseInt($(this).attr('rel'))<=parseInt($(obj).attr('rel')))
            $(this).addClass('ag-shop-slider-card__rating-item--active');
    })
    
}

function loadComments(){
    var productId=$('.ag-shop-card__review-form').attr("prodictid");
    $('.ag-shop-card__reviews').load("/local/components/ag/card/comments.ajax.php?productid="+productId);
}

function productConfirm(){
    $('#card-order-confirm').fadeIn();
    $('#confirm-name').html($('.ag-shop-card__header-title').html());
    $('#confirm-price span').html($('.ag-shop-item-card__points-count').html());
    $('#confirm-unit').html($('.ag-shop-card__total-points').html());
    $('#confirm-amount').html($('.ag-shop-card__count-number').html());
    $('#confirm-cost span').html($('#ag-shop-card__total-points').html());
    $('#confirm-store').html($('.ag-shop-card__selected-place-station').html());
    $('#confirm-store-id').html(totalStoreId);
}

function productConfirmNext(){
    var add_basket_url = "/profile/order/order.ajax.php?add_to_basket=1&id="
    +totalOfferId
    +"&quantity="+$('#confirm-amount').html()
    +"&store_id="+$('#confirm-store-id').html();
    
    // добавляем в корзину
    $('#card-order-confirm-button').html('Обработка заказа...')
    $.get(
        add_basket_url,
        function(data){
            data = data.replace(/'/gi,'"');
            var answer = JSON.parse(data);
            if(answer.STATUS!='OK'){
                ag_ci_rise_error(answer.MESSAGE);
                return false;
            }
            
            $.get(
                "/profile/order/order.ajax.php?add_order=Y&store_id="+answer.store_id,
                function(data){
                    var answer = JSON.parse(data);
                    if(answer.redirect_url){
                        document.location.href=answer.redirect_url;
                    }
                    else{
                        // Чистим корзину, если заказ неудачен
                        $.get(
                            "/profile/order/order.ajax.php?clear_basket",
                            function(){
                                $('#order-process-done').css('display','none');
                                $('.ok-button').css('display','block');
                                var error_text = '';
                                for(i in answer.order.ERROR){
                                    error_text += i+": "+answer.order.ERROR[i];
                                }
                                $('.ag-shop-modal-wrap').fadeOut('fast');
                            }
                        );
                    }
                }
            );
        }
    );
}


function showCommonFeedbackForm(){
    $('#common-feedback-form').fadeIn();
    return false;
}

function hideCommonFeedbackForm(){
    $('#common-feedback-form').fadeOut();
    return false;
}

function sendCommonFeedbackForm(){

    var post = {};
    post["type"] = $('#feedback_type').val();
    post["name"] = $('#feedback_name').html();
    post["text"] = $('#feedback_text').val();

    if(!post["text"]){
        alert('Введите сообщение');
        return false;
    }
    if(!post["type"]){
        alert('Выберите тип обращения');
        return false;
    }
    
    $('#common-feedback-form .ag-shop-modal__container div').hide();
    $('#common-feedback-form .ag-shop-modal__container').prepend('<div class="form-success">Сообщение отправляется</div>');

    $.post(
        "/profile/common_feedback.ajax.php",
        post,
        function(data){
            setTimeout(function(){
                $('#common-feedback-form').fadeOut(function(){
                    $('#common-feedback-form .ag-shop-modal__container div').show();
                    $('#common-feedback-form .ag-shop-modal__container .form-success').remove();        
                });
            },2000);
        }
    );

    
    return false;
}

function showOrdersFeedbackForm(orderNum){
    $('#order-feedback-form-ordernum').html(orderNum);
    $('#orders-feedback-form').fadeIn();
    return false;
}

function hideOrdersFeedbackForm(){
    $('#orders-feedback-form').fadeOut();
    return false;
}

function sendOrdersFeedbackForm(){

    var post = {};
    post["order"] = $('#order-feedback-form-ordernum').html();
    post["type"] = $('#order-feedback-form-type').val();
    post["name"] = $('#order-feedback-form-fio').html();
    post["text"] = $('#order-feedback-form-text').val();
    if(!post["text"]){
        alert('Введите сообщение');
        return false;
    }
    if(!post["type"]){
        alert('Выберите тип обращения');
        return false;
    }

    $('#orders-feedback-form .ag-shop-modal__container div').hide();
    $('#orders-feedback-form .ag-shop-modal__container').prepend('<div class="form-success">Сообщение отправляется</div>');
    
    $.post(
        "/profile/order_feedback.ajax.php",
        post,
        function(data){
            setTimeout(function(){
                $('#orders-feedback-form').fadeOut(function(){
                    $('#orders-feedback-form .ag-shop-modal__container div').show();
                    $('#orders-feedback-form .ag-shop-modal__container .form-success').remove();        
                });
            },2000);
        }
    );
    return false;
}

function printOrder(orderId){
    $('body').append('<iframe style="display:none;" src="/profile/order/print.ajax.php?id='+orderId+'"></iframe>');
    return false;
}

function orderCancel(orderId, obj){
    $(obj).unbind("click");
    $(obj).attr("onclick","return false;");
    $(obj).html('Производится отмена...');
    $.get(
        "/profile/order/order.ajax.php?cancel="+orderId,
        function(data){
            var answer = JSON.parse(data);
            if(answer.error){
                alert(answer.error);
                return false;
            }
            document.location.href='/profile/order/?tab=use';
        }
    );
    return false;
}



