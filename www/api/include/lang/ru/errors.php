<?
    $ERR_MESS = array();
    $ERR_MESS[CAll::ERROR_NO_REQUEST]      
        = 'Пустой запрос';
    $ERR_MESS[CAll::ERROR_WRONG_REQUEST]   
        = 'Некореектный запрос (ошибка json)';
    $ERR_MESS[CAll::ERROR_NO_METHOD]       
        = 'Не указан метод';
    $ERR_MESS[CAll::ERROR_METHOD_NOT_EXISTS]   
        = 'Метод не существует';
    $ERR_MESS[CAll::ERROR_INIT_METHOD]   
        = 'Ошибка инициализации метода';
    $ERR_MESS[CAll::ERROR_CHECK_ARGS]   
        = 'Ошибка инициализации метода';
    $ERR_MESS[CAll::ERROR_EXECUTE_UNKNOWN]   
        = 'Неизвестная ошибка выполнения';
    $ERR_MESS[CAll::ERROR_AUTH_ERROR]   
        = 'Ошибка авторизации';
    $ERR_MESS[CAll::ERROR_SESSION_NOT_DEFINED]   
        = 'Сессия не указана';
    $ERR_MESS[CAll::ERROR_SESSION_INCORRECT]   
        = 'Некорректный ID сессии';


if(class_exists("CMethod_auth")){
    $ERR_MESS[CMethod_auth::ERROR_AUTH_EMP_CONNECTION]   
        = 'Ошибка соединения с emp.mos.ru';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_JSON_PARSE]   
        = 'Ошибка парсинга JSON';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_NO_SESSION_ID]   
        = 'Сессия не вернулась';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_NO_PROFILE_RESULT]   
        = 'Вернулся постой профиль';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_EMP_NO_SSOID]   
        = 'Вернулся пустой emp-sso_id';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_IS_EMPTY]   
        = 'Аргумент не задан';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_LOGIN_IS_NOT_EXISTS]   
        = 'Логин не задан';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_PASSWORD_IS_NOT_EXISTS]   
        = 'Пароль не задан';

}

if(class_exists("CMethod_transaction")){
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_IS_EMPTY]   
        = 'Аргументы не заданы';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_APP_ID_EMPTY]   
        = 'ID приложения не задан';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_APP_ID_UNDEFINED]   
        = 'Неизвестное ID приложения';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_POINTS_INCORRECT]   
        = 'Некорректное число баллов';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_DEBIT_INCORRECT]   
        = 'Некорректное направление начисления';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_COMMENT_EMPTY]   
        = 'Не указан комментарий';
}

if(class_exists("CMethod_points")){
    $ERR_MESS[CMethod_points::ERROR_POINTS_IS_EMPTY]   
        = 'Аргументы не заданы';
    $ERR_MESS[CMethod_points::ERROR_POINTS_APP_ID_UNDEFINED]   
        = 'Неизвестное ID приложения';
}


