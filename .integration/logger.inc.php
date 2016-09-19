<?php

    $REQUEST_FOLDER = "upload/1c_exchange/logger";
    $FILES_FOLDER = "upload/1c_exchange/logger/files";
    $REQUEST_KEY = date("Y-m-d-H-i-s").rand(0,10000);
    
    mkdirs($REQUEST_FOLDER);
    mkdirs($REQUEST_FOLDER."/files/".$REQUEST_KEY);
    
    $filename = $_SERVER["DOCUMENT_ROOT"]."/".$REQUEST_FOLDER."/".$REQUEST_KEY.".get";
    @file_put_contents($filename, print_r($_GET,1));
    $filename = $_SERVER["DOCUMENT_ROOT"]."/".$REQUEST_FOLDER."/".$REQUEST_KEY.".post";
    @file_put_contents($filename, print_r($_POST,1));
    $filename = $_SERVER["DOCUMENT_ROOT"]."/".$REQUEST_FOLDER."/".$REQUEST_KEY.".files";
    @file_put_contents($filename, print_r($_FILES,1));
    
    foreach($_FILES as $file){
        $tmp_name = str_replace("/","_",$file["tmp_name"]);
        @copy($file["tmp_name"], $REQUEST_FOLDER."/files/".$REQUEST_KEY."/".$tmp_name);
    }
    
    
    
    
    function mkdirs($REQUEST_FOLDER){
        $folders = explode("/", $REQUEST_FOLDER);
        $path = "";
        foreach($folders as $folder){
            $path .= "/".$folder;
            $current_path = $_SERVER["DOCUMENT_ROOT"].$path;
            if(!is_dir($current_path))@mkdir($current_path);
        }
    }
