//Начало : Функционал для кнопки возврата 

$(document).ready(function(){

 $('.wrap_back_btn').on('click', function(){

  window.history.back();
  return false;
 });
})

//Конец : Функционал для кнопки возврата 
