<?
if(CONTOUR=='prod' || (isset($_COOKIE["EMPSESSION"]) &&
$_COOKIE["EMPSESSION"])){
}
else{
    $sPassFilename = realpath(
       $_SERVER["DOCUMENT_ROOT"]."/../etc/nginx/auth/auth2.passwd"
    );

    $arLines = file($sPassFilename);
    $bIsAuth = false;
    foreach($arLines as $sLine){
        $tmp = explode(":", $sLine);
        $sUsername = trim($tmp[0]);
        if(!isset($tmp[1]) || !$tmp[1])continue;
        $sPassword = trim($tmp[1]);
        if(
            $sUsername == $_SERVER['PHP_AUTH_USER']
            &&
            $_SERVER['PHP_AUTH_PW'] == $sPassword
        )$bIsAuth = true;
    }

    if (!$bIsAuth) {
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        include($_SERVER["DOCUMENT_ROOT"]."/403.php");

        exit;
    }
}



