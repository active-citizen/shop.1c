var webPage = require('webpage');
var page = webPage.create();
var fs = require('fs');
var error_flag_file = "tmp/pj_error";
if(fs.exists(error_flag_file))
    fs.remove(error_flag_file);


// Массив действий со страницей
var datas = Array();
var do_it='';

{Install:data}


page.viewportSize = { width: 800, height: 600 };
page.open('{{Hosting::http}}',function(){
    do_action(0);
});

function do_action(act_num){
    // Выходим, если делать буольше ничего не надо
    if(!datas[act_num])phantom.exit();
    
    do_it = datas[act_num];
    var loop=0;

    // Делаем скриншот до
    if(do_it.render)page.render("screens/"+do_it.render);
    
    // Это отрабатывает после того, как страница после клика перезагрузится
    page.onLoadFinished = function(){
        
        var script = '';
        // Запетление по условию или переход к следующему шагу
        if(do_it.loop_cond){
            script = 'function(){return '+do_it.loop_cond+";}";
            if(result = page.evaluateJavaScript(script)){
                console.log("LOOP "+loop);
                page.render("screens/"+do_it.render+'.loop.'+loop+'.png');
                loop++;
                return true;
            }
        }
        
        page.render("screens/"+do_it.render+'.after.png');
        page.onLoadFinished = function(){};
        do_action(act_num+1);
    }
    
    // Проверка стопа
    if(do_it.stop_cond){
        var script = 'function(){return '+do_it.stop_cond+";}";
        console.log("CHECKING: "+script);
        var result = '';
        if(result = page.evaluateJavaScript(script)){
            console.log("ERROR ANSWER="+result+'!!!');
            fs.write(error_flag_file,"Step "+act_num+', checking result = '+result, "w");
            phantom.exit();
        }
    }
    
    // Заполняем поля, делаем клики
    for(i in do_it.clicks){
        console.log("CLICK: "+do_it.clicks[i]);
        page.evaluate(
            function(selector){
                document.querySelector(selector).click();
            }
            ,
            do_it.clicks[i]
        );
    }
    for(i in do_it.inputs){
        console.log("INPUT: "+do_it.inputs[i].name+"="+do_it.inputs[i].value);
        page.evaluate(
            function(selector,value){
                document.querySelector(selector).value = value;
            }
            ,
            do_it.inputs[i].name
            ,
            do_it.inputs[i].value
        );
    }
    
    // Делаем на странице финальный клик
    if(do_it.final_click){
        console.log("FINALI-CLICK: "+do_it.final_click);
        page.evaluate(
            function(selector){
                document.querySelector(selector).click();
            }
            ,
            do_it.final_click
        );
        if(do_it.render)page.render("screens/"+do_it.render+'.middle.png');
    }
}
