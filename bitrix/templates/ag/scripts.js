$(document).ready(function(){
    
    
    
    $('.bx_cart_ag').click(function(){
        var offer_id = 0;
        
        // Определяем ID выбранного товарного предложения
        $('.bx_slider_conteiner').each(function(){
            if($(this).css('display')!='none'){
                offer_id = $(this).find('.bx_slide_left').attr("data-value");
                return false;
            }
        });
        if(!offer_id){ag_ci_rise_error("Неизвестный ID товарного каталога");return false;}
        
        // Определяем ID выбранного склада
        var store_id = 0;
        var default_store_id = 0;
        var stores_count = 0;
        $('#c_store_amount li').each(function(){
            if($(this).css('display')!='none'){
                default_store_id = $(this).find('input').val();
                stores_count++;
                if($(this).find('input').attr('checked')=='checked')store_id = $(this).find('input').val();
            }
        });
        // Если центр выдачи один - его и назначаем
        if(stores_count==1 && default_store_id)store_id = default_store_id;
        if(!store_id){ag_ci_rise_error("Центр выдачи не выбран, либо товара нет в наличии");return false;}

        var get_profile_url = "/order/order.ajax.php?offer_id="+offer_id+'&store_id='+store_id;


        // Получаем информацию по профилю пользователя
        var profile = {};
        var product = {};
        var store = {};
        $.get(get_profile_url,function(data){
            var answer = JSON.parse(data);
            if(!answer.profile){
                ag_ci_rise_error('Ошибка запроса профиля, склада, товара:'+answer.error);
                return false;
            }
            profile = answer.profile; 
            product = answer.product; 
            store = answer.store;
    console.log(profile);
            if(!profile.ID){
                ag_ci_rise_error('Ошибка получения профиля');
                return false;
            }
            // Выводим окно подтверждения заказа
            ag_ci_rise_confirm(profile,store,product);
        });
        
        
        
        return false;
        
        
        return false;
    });
    
    // Заказ из окна подтверждения
    $('.catalog_item_confirm_message .ok-button').click(function(){

        var offer_id = $('.catalog_item_confirm_message .ag-window #offer_id').html();
        if(!offer_id){
            ag_ci_rise_error('Не определён ID торгового предложения');
            return false;
        }
        
        var add_basket_url = "?action=BUY&id="+offer_id+"&ajax_basket=Y";

        // добавляем в корзину
        $.get(
            add_basket_url,
            function(data){
                data = data.replace(/'/gi,'"');
                var answer = JSON.parse(data);
                if(answer.STATUS!='OK'){
                    ag_ci_rise_error(answer.MESSAGE);
                    return false;
                }
                
                    var postdata = {
                        "sessid":           $('.catalog_item_confirm_message .ag-window #sess_id').html(),
                        "action":           "saveOrderAjax",
                        "location_type":    "code",
                        "BUYER_STORE":      $('.catalog_item_confirm_message .ag-window #store_id').html(),
                        "DELIVERY_ID":      3,
                        "save":             "Y",
                    }
                    $.post(
                        "/order/make/",
                        postdata,
                        function(data){
                            var answer = JSON.parse(data);
                            if(answer.order.REDIRECT_URL){
                                document.location.href=answer.order.REDIRECT_URL;
                            }
                            else{
                                ag_ci_rise_error(answer.order.ERROR.MAIN);
                            }
                        }
                    );
            }
        );
        return false;
    });
    
    $('.catalog_item_error_message .ag-window .close-button').click(function(){
        $('.catalog_item_error_message').fadeOut('fast');
    });

    $('.catalog_item_confirm_message .ag-window .close-button').click(function(){
        $('.catalog_item_confirm_message').fadeOut('fast');
    });
    
});

function ag_ci_rise_error(text){
    $('.catalog_item_error_message .ag-window .message').html(text);
    $('.catalog_item_error_message').fadeIn('fast');
}

function ag_ci_rise_confirm(profile,store,product){
    $('.catalog_item_confirm_message .ag-window #offer').html(product.ELEMENT_NAME);
    $('.catalog_item_confirm_message .ag-window #store').html(store.TITLE+'<br/>'+store.ADDRESS);
    $('.catalog_item_confirm_message .ag-window #offer_id').html(product.ID);
    $('.catalog_item_confirm_message .ag-window #store_id').html(store.ID);
    $('.catalog_item_confirm_message .ag-window #sess_id').html(profile.SESSID);
    $('.catalog_item_confirm_message .ag-window .message').html();
    $('.catalog_item_confirm_message').fadeIn('fast');
}
