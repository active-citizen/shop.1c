$(function() {

  // ===================
  // Animation for header
  // ===================

  $(".mobile-header").headroom({
    "offset": 20,
    "tolerance": 5
  });


  // Cross Browser MaxLength
  $("input[type=number]").on('blur',function(){ 
      var $that = $(this),
      maxlength = $that.attr('maxlength')
      if($.isNumeric(maxlength)){
          $that.val($that.val().substr(0, maxlength));
      };
  });

  // ===================
  // Cross Browser Width Calc
  // ===================

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


});
