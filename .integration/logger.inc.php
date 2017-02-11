<?php

    $REQUEST_FOLDER = "upload/1c_exchange/logger";
    $REQUEST_KEY = date("Y-m-d-H-i-s-").microtime(true);

    mkdirs($REQUEST_FOLDER);

    $filename = $_SERVER["DOCUMENT_ROOT"]."/".$REQUEST_FOLDER."/".$REQUEST_KEY.".headers";
    $fd = fopen($filename, "w");
    $headers = request_headers();
    fwrite($fd, ($_POST?"POST ":"GET ")." ".$_SERVER["REQUEST_URI"]."\n");
    foreach($headers as $hname=>$hvalue)fwrite($fd, trim($hname).":".trim($hvalue)."\n");
    fclose($fd);
    
    $filename = $_SERVER["DOCUMENT_ROOT"]."/".$REQUEST_FOLDER."/".$REQUEST_KEY.".data";
    $fd = fopen("php://input", "r");
    $fd2= fopen($filename,"w");
    while(!feof($fd))fwrite($fd2, fread($fd,1000));
    fclose($fd2);
    fclose($fd);

    function mkdirs($REQUEST_FOLDER){
        $folders = explode("/", $REQUEST_FOLDER);
        $path = "";
        foreach($folders as $folder){
            $path .= "/".$folder;
            $current_path = $_SERVER["DOCUMENT_ROOT"].$path;
            if(!is_dir($current_path))@mkdir($current_path);
        }
    }
    
    function request_headers(){
        $result = array();
        foreach($_SERVER as $key=>$value)
            if(preg_match("/^HTTP\_(.*)$/i",$key,$m))
                $result[str_replace("_","-",$m[1])] = $value;
        return $result;
    }
