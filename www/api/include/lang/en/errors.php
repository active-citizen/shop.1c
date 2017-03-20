<?
    $ERR_MESS = array();
    $ERR_MESS[CAll::ERROR_NO_REQUEST]          
        = 'Empty request';
    $ERR_MESS[CAll::ERROR_WRONG_REQUEST]       
        = 'Incorrect request (wrong json)';
    $ERR_MESS[CAll::ERROR_NO_METHOD]           
        = 'Method not specified';
    $ERR_MESS[CAll::ERROR_METHOD_NOT_EXISTS]   
        = 'Method is not exists';
    $ERR_MESS[CAll::ERROR_INIT_METHOD]   
        = 'Init Method is not exists';
    $ERR_MESS[CAll::ERROR_CHECK_ARGS]   
        = 'Arguments check error';
    $ERR_MESS[CAll::ERROR_EXECUTE_UNKNOWN]   
        = 'Unknown execution error';
    $ERR_MESS[CAll::ERROR_AUTH_ERROR]   
        = 'Auth error';
    $ERR_MESS[CAll::ERROR_SESSION_NOT_DEFINED]   
        = 'Session is not defined';
    $ERR_MESS[CAll::ERROR_SESSION_INCORRECT]   
        = 'Session ID is incorrect';


if(class_exists("CMethod_auth")){
    $ERR_MESS[CMethod_auth::ERROR_AUTH_EMP_CONNECTION]   
        = 'emp.mos.ru connection error';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_JSON_PARSE]   
        = 'Answer parsing error from emp.mos.ru';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_NO_SESSION_ID]   
        = 'Session ID is empty';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_NO_PROFILE_RESULT]   
        = 'No profile data';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_EMP_NO_SSOID]   
        = 'No emp-sso_id';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_IS_EMPTY]   
        = 'Args is empty';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_LOGIN_IS_NOT_EXISTS]   
        = 'Login is not exists';
    $ERR_MESS[CMethod_auth::ERROR_AUTH_PASSWORD_IS_NOT_EXISTS]   
        = 'Password is not exists';
}

if(class_exists("CMethod_transaction")){
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_IS_EMPTY]   
        = 'Empty arguments';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_APP_ID_EMPTY]   
        = 'Application ID undefined';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_APP_ID_UNDEFINED]   
        = 'Unknown Application ID';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_POINTS_INCORRECT]   
        = 'Points quantity is incorrent';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_DEBIT_INCORRECT]   
        = 'Debit direction is incorrect';
    $ERR_MESS[CMethod_transaction::ERROR_TRANSACTION_COMMENT_EMPTY]   
        = 'Comment is empty';
}

if(class_exists("CMethod_points")){
    $ERR_MESS[CMethod_points::ERROR_POINTS_IS_EMPTY]   
        = 'Empty arguments';
    $ERR_MESS[CMethod_points::ERROR_POINTS_APP_ID_UNDEFINED]   
        = 'Unknown Application ID';
}


