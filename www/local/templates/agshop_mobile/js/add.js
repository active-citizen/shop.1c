//Начало : Функционал для кнопки возврата 

$(document).ready(function(){

 $('.mobile-header-back__link').on('click', function(){

  window.history.back();
  return false;
 });
})

//Конец : Функционал для кнопки возврата 
