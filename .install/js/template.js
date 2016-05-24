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
    page.render("screens/step-"+act_num+'.start.png');
    fs.write("screens/step-"+act_num+'.start.html',page.content);
    if(!datas[act_num])phantom.exit();
    
    do_it = datas[act_num];
    var loop=0;
    /*verbose*/console.log("STEP: "+act_num);/*end verbose*/

    // Делаем скриншот до
    
    // Это отрабатывает после того, как страница после клика перезагрузится
    page.onLoadFinished = function(){
        
        var script = '';
        // Запетление по условию или переход к следующему шагу
        if(do_it.loop_cond){
            script = 'function(){return '+do_it.loop_cond+";}";
            if(result = page.evaluateJavaScript(script)){
                /*verbose*/console.log("LOOP "+loop);/*end verbose*/
                loop++;
                return true;
            }
        }
        
        page.onLoadFinished = function(){};
        do_action(act_num+1);
    }


    // Проверка наличия блока с ошибками
    var script = "function(){if (document.querySelector('.inst-note-block-red .inst-note-block-text') && document.querySelector('.inst-note-block-red').parentNode.parentNode.style['display']!='none')return document.querySelector('.inst-note-block-red .inst-note-block-text').innerHTML;}";
    var result = '';
    if(result = page.evaluateJavaScript(script)){
        if(do_it.render)page.render("screens/"+act_num+'.png');
        console.log("ERROR: "+result+"");
        phantom.exit();
    }

    // Проверка наличия красной строчки
    /*
    var script = "function(){if (document.querySelector('p[style=\"color:red\"]'))return document.querySelector('p[style=\"color:red\"]').innerHTML;}";
    var result = '';
    if(result = page.evaluateJavaScript(script)){
        if(do_it.render)page.render("screens/"+act_num+'.png');
        console.log("ERROR: "+result+"");
        phantom.exit();
    }
    */
    
    
    // Проверка стопа по условию
    if(do_it.stop_cond){
        var script = 'function(){return '+do_it.stop_cond+";}";
        /*verbose*/console.log("CHECKING: "+script);/*end verbose*/
        var result = '';
        if(result = page.evaluateJavaScript(script)){
            console.log("ERROR: "+result+' См. скриншоты');
            phantom.exit();
        }
    }
    
    // Заполняем поля, делаем клики
    for(i in do_it.clicks){
        /*verbose*/console.log("CLICK: "+do_it.clicks[i]);/*end verbose*/
        page.evaluateJavaScript("function(){"+do_it.clicks[i]+"}");
        /*
        page.evaluate(
            function(selector){
                if(document.querySelector(selector))document.querySelector(selector).click();
            }
            ,
            do_it.clicks[i]
        );
        */
    }
    for(i in do_it.inputs){
        /*verbose*/console.log("INPUT: "+do_it.inputs[i].name+"="+do_it.inputs[i].value);/*end verbose*/
        page.evaluate(
            function(selector,value){
                if(document.querySelector(selector))document.querySelector(selector).value = value;
            }
            ,
            do_it.inputs[i].name
            ,
            do_it.inputs[i].value
        );
    }

    page.render("screens/step-"+act_num+'.finish.png');
    fs.write("screens/step-"+act_num+'.finish.html',page.content);
    
    // Делаем на странице финальный клик
    if(do_it.final_click){
        /*verbose*/console.log("FINALI-CLICK: "+do_it.final_click);/*end verbose*/
        page.evaluate(
            function(selector){
                document.querySelector(selector).click();
            }
            ,
            do_it.final_click
        );
    }
}

