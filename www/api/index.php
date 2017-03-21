<?php
    require("include/common.php");

    $CSession = new CSession;

    $_POST["request"] = 
        '
        {
            "method":"auth",
            "args":{
                "login":"79277063223",
                "password":"***********"
            }
        }
        ';

    /*
    $_POST["request"] = 
        '
        {
            "method":"transaction",
            "args":{
                "appId":"1",
                "points":"10",
                "debit": "-1"
            },
            "session_id":"1570274ff3645af9512f6b92bfcff780"
        }
        ';
    */
    /*
    $_POST["request"] = 
        '
        {
                    "method":"points",
                    "args":{
                    },
                    "session_id":"2780177665c8bb857e5c8049ecbf2637"
                }        
        ';
    
    */
    
    $sLang = 'en';


    $objRequest = json_decode(isset($_POST["request"])?$_POST["request"]:"{}");
    if(!$objRequest)$objRequest = json_decode("{}");
    if($objRequest && !property_exists($objRequest, "args"))$objRequest->args = "";
    
    if(!isset($_POST["request"]) || !$_POST["request"]){
        echo json_encode(array(
            "errorCode" =>  CAll::ERROR_NO_REQUEST,
            "errorMessage" =>  CAll::getErrorMessage(CAll::ERROR_NO_REQUEST)
        ));
        exit;
    }
    elseif(!$objRequest){
        echo json_encode(array(
            "errorCode" =>  CAll::ERROR_WRONG_REQUEST,
            "errorMessage" =>  CAll::getErrorMessage(CAll::ERROR_WRONG_REQUEST)
        ));
        exit;
    }
    elseif(!property_exists($objRequest,"method")){
        echo json_encode(array(
            "errorCode" =>  CAll::ERROR_NO_METHOD,
            "errorMessage" =>  CAll::getErrorMessage(CAll::ERROR_NO_METHOD)
        ));
        exit;
    }
    elseif( !$sMethodClassName = CMethod::exists($objRequest->method) ){
        echo json_encode(array(
            "errorCode" => CAll::ERROR_METHOD_NOT_EXISTS,
            "errorMessage" => CAll::getErrorMessage(CAll::ERROR_METHOD_NOT_EXISTS)
        ));
        exit;
    }
    elseif(
        $objRequest->method!='auth' && property_exists($objRequest,"session_id")
        && !preg_match("#^[0-9a-f]+$#",$objRequest->session_id)
    ){
        echo json_encode(array(
            "errorCode" => CAll::ERROR_SESSION_INCORRECT,
            "errorMessage" => CAll::getErrorMessage(CAll::ERROR_SESSION_INCORRECT)
        ));
        exit;
    }
    elseif(
        $objRequest->method!='auth' && !property_exists($objRequest,"session_id")
    ){
        echo json_encode(array(
            "errorCode" => CAll::ERROR_SESSION_NOT_DEFINED,
            "errorMessage" => CAll::getErrorMessage(CAll::ERROR_SESSION_NOT_DEFINED)
        ));
        exit;
    }
    elseif(
        $objRequest->method!='auth' && (
            !property_exists($objRequest,"session_id")
            || 
            !$GLOBALS["sessions"] = $CSession->get($objRequest->session_id)
        )
    ){
        echo json_encode(array(
            "errorCode" => CAll::ERROR_AUTH_ERROR,
            "errorMessage" => CAll::getErrorMessage(CAll::ERROR_AUTH_ERROR)
        ));
        exit;
    }
    elseif(!$objMethod = new $sMethodClassName($sLang, $objRequest->method=='auth'?"":$objRequest->session_id)){
        echo json_encode(array(
            "errorCode" => CAll::ERROR_INIT_METHOD,
            "errorMessage" => CAll::getErrorMessage(CAll::ERROR_INIT_METHOD)
        ));
        exit;
    }
    elseif($error = $objMethod->argsError($objRequest->args, $sLang)){
        echo json_encode(array(
            "errorCode" => CAll::ERROR_CHECK_ARGS, 
            "errorMessage" => 
                $error!==true
                ?
                $error
                :
                CAll::getErrorMessage(CAll::ERROR_CHECK_ARGS)

        ));
        exit;
    }
    elseif(
        $answer = $objMethod->go($objRequest->args) 
    ){
        echo json_encode(array(
            "errorCode" =>  0, 
            "errorMessage" =>  '',
            "result"=>$answer
        ));
    }
    elseif($objMethod->getErrorNo()){
        echo json_encode(array(
            "errorCode" =>  $objMethod->getErrorNo(), 
            "errorMessage" =>  $objMethod->getErrorMessage(
                $objMethod->getErrorNo() 
            )
        ));
        exit;
    }
    else{
        echo json_encode(array(
            "errorCode" =>  CAll::ERROR_EXECUTE_UNKNOWN, 
            "errorMessage" =>  CAll::getErrorMessage(CAll::ERROR_EXECUTE_UNKNOWN) 
        ));
        exit;
    }



