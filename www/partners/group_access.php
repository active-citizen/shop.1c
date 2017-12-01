<?
// Если пользователь - не админ магазина и не общий админ - редиректим
if(
    !in_array(SHOP_ADMIN, $USER->GetUserGroupArray())
    && !$USER->IsAdmin()
   
){
    LocalRedirect("/partners/orders/");
    die;
}

