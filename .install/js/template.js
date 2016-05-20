var webPage = require('webpage');
var page = webPage.create();

// Массив действий со страницей
var datas = Array();

{Install:data}


page.viewportSize = { width: 800, height: 600 };
page.open('{{Hosting::http}}',function(){
    do_action(0);
});

function do_action(act_num){
    // Выходим, если делать буольше ничего не надо
    if(!datas[act_num])phantom.exit();
    
    var do_it = datas[act_num];

    // Делаем скриншот до
    if(do_it.render)page.render("screens/"+do_it.render);
    
    // Это отрабатывает после того, как страница после клика перезагрузится
    page.onLoadFinished = function(){
        // Делаем скриншот после
        if(do_it.render)page.render("screens/"+do_it.render+'.after.png');
        page.onLoadFinished = function(){};
        do_action(act_num+1);
    }
    
    // Заполняем поля, делаем клики
    for(i in do_it.clicks){
        page.evaluate(
            function(selector){
                document.querySelector(selector).click();
            }
            ,
            do_it.clicks[i]
        );
    }
    
    // Делаем на странице финальный клик
    page.evaluate(
        function(selector){
            document.querySelector(selector).click();
        }
        ,
        do_it.final_click
    );
    
}
