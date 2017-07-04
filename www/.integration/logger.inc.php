<?php

    ob_start("logger_save_buffer");

    require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/settings.inc.php");
    $REQUEST_FOLDER = "upload/logger".(CONTOUR!='prod'?"_".CONTOUR:"");
    $REQUEST_KEY = date("Y-m-d-H-i-s-").microtime(true);

    $REQUEST_FOLDER = $_SERVER["DOCUMENT_ROOT"]."/".$REQUEST_FOLDER;
    if(!is_dir($REQUEST_FOLDER))@mkdir($REQUEST_FOLDER);
    $REQUEST_FOLDER .= "/".date("Y-m");
    if(!is_dir($REQUEST_FOLDER))@mkdir($REQUEST_FOLDER);
    $REQUEST_FOLDER .= "/".date("Y-m-d");
    if(!is_dir($REQUEST_FOLDER))@mkdir($REQUEST_FOLDER);
    $REQUEST_FOLDER .= "/".date("Y-m-d_H")."h";
    if(!is_dir($REQUEST_FOLDER))@mkdir($REQUEST_FOLDER);


    $filename = $REQUEST_FOLDER."/".$REQUEST_KEY.".output.data";
    define("LOGGER_OUTPUT_FILENAME", $filename);

    //mkdirs($REQUEST_FOLDER);

    $filename = $REQUEST_FOLDER."/".$REQUEST_KEY.".input.headers";
    $fd = fopen($filename, "w");
    $headers = request_headers();
    fwrite($fd, ($_POST?"POST ":"GET ")." ".$_SERVER["REQUEST_URI"]."\n");
    foreach($headers as $hname=>$hvalue)fwrite($fd, trim($hname).":".trim($hvalue)."\n");
    fclose($fd);
    
    $filename = $REQUEST_FOLDER."/".$REQUEST_KEY.".input.data";
    define("LOGGER_INPUT_FILENAME", $filename);
    $fd = fopen("php://input", "r");
    $fd2= fopen($filename,"w");
    while(!feof($fd))fwrite($fd2, fread($fd,1000));
    fclose($fd2);
    fclose($fd);



    $zip = new ZipArchive();
    if($zip->open($filename)){
        if($zip->numFiles){
            for($i=0,$c=$zip->numFiles;$i<$c;$i++){
                $arZipStat = $zip->statIndex($i);
                if(!preg_match("#^.*\.xml$#", $arZipStat["name"]))continue;
                if(preg_match("#\/#", $arZipStat["name"]))continue;

                if($fd = $zip->getStream($arZipStat["name"])){
                    $wd = fopen($filename.".".$arZipStat["name"],"w");
                    while(!feof($fd)) fwrite($wd,fread($fd,1000));
                    fclose($wd);
                }
                if($fd)fclose($fd);
            }
        }
    }

    


    $filename = $REQUEST_FOLDER."/".$REQUEST_KEY.".output.headers";
    $fd = fopen($filename, "w");
    $headers = headers_list();
    fwrite($fd, implode("\n", $headers));
    foreach($headers as $hname=>$hvalue)fwrite($fd, trim($hname).":".trim($hvalue)."\n");
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

    function logger_save_buffer($data){
        $fd = fopen(LOGGER_OUTPUT_FILENAME,"w");
        fwrite($fd,$data);
        fclose($fd);
        return $data;
    }
