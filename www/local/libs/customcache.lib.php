<?php
    /*
        Работа с ручным html-кешем страниц (в обход битрикса)
    */
    define("CUSTOM_CACHE_DIR", $_SERVER["DOCUMENT_ROOT"]."/upload/custom_cache");

    function customCache($lifetime = 86400,$sUserLogin = 0){
        // Если пользователь кэша не указан - используем текущего из кук
        if(
            !$sUserLogin 
            && 
            isset($_COOKIE["LOGIN"])
            &&
            $_COOKIE["LOGIN"]
        ) $sUserLogin = $_COOKIE["LOGIN"];

        // Если пользователь анонимус - то туда ему и дорога
        if(!$sUserLogin)$sUserLogin = 'anonymous';

        $sHash = md5($_SERVER["REQUEST_URI"]);
        
        $sHashFilename = CUSTOM_CACHE_DIR."/"
            .mb_substr($sUserLogin,-1,1)."/"
            .mb_substr($sUserLogin,-2,1)."/"
            .mb_substr($sUserLogin,-3,1)."/"
            .mb_substr($sUserLogin,-4,1)."/"
            .mb_substr($sUserLogin,-5,1)."/"
            .mb_substr($sUserLogin,-6,1)."/"
            .mb_substr($sUserLogin,-7,1)."/"
            .mb_substr($sUserLogin,-8,1)."/"
            .mb_substr($sUserLogin,-9,1)."/"
            .$sUserLogin."/"
            .mb_substr($sHash, 0, 1)."/"
            .mb_substr($sHash, 1, 1)."/"
            .mb_substr($sHash, 2, 1)."/"
            .$sHash.".html";

        define("CUSTOM_CACHE_HASH_FILENAME", $sHashFilename);

        $bRefresh = true;
        if(file_exists($sHashFilename)){
            $stat = stat($sHashFilename);
            if(time() - $stat["mtime"]<$lifetime)$bRefresh = false;

        }
        if($bRefresh){
            ob_start("customCacheStore");
        }
        else{
            //header("Content-Encoding:gzip");
            echo file_get_contents($sHashFilename);
            die;
        }

    }
    
    /**
        Сохранение вывода скрипта в кэш
    */
    function customCacheStore($data){
        $sFilepath = str_replace(CUSTOM_CACHE_DIR,"",CUSTOM_CACHE_HASH_FILENAME);
        $arPath = explode("/",$sFilepath);
        $sCurrentDir = CUSTOM_CACHE_DIR;
        for($i=1,$c=count($arPath);$i<$c-1;$i++){
            $sCurrentDir .= '/'.$arPath[$i];
            if(!is_dir($sCurrentDir))mkdir($sCurrentDir);
        }
    
        $fd = fopen(CUSTOM_CACHE_HASH_FILENAME,"w");
        fwrite($fd, $data);
        fclose($fd);
                 
        return $data;
    }

    /**
        Чистка ручного кэша
    */
    function customCacheClear($sDir = '',$sUserLogin=''){
        if(!$sDir && !$sUserLogin)
            $sDir = CUSTOM_CACHE_DIR;
        elseif(!$sDir && $sUserLogin)
            $sDir = 
                CUSTOM_CACHE_DIR."/"
                .mb_substr($sUserLogin,-1,1)."/"
                .mb_substr($sUserLogin,-2,1)."/"
                .mb_substr($sUserLogin,-3,1)."/"
                .mb_substr($sUserLogin,-4,1)."/"
                .mb_substr($sUserLogin,-5,1)."/"
                .mb_substr($sUserLogin,-6,1)."/"
                .mb_substr($sUserLogin,-7,1)."/"
                .mb_substr($sUserLogin,-8,1)."/"
                .mb_substr($sUserLogin,-9,1)."/"
                .$sUserLogin;

        $arFiles = scandir($sDir);
        foreach($arFiles as $filename){
            if($filename=='.' || $filename=='..' || $filename=='.htaccess')
                continue;
            $sCurrentFilename = $sDir."/".$filename;

            if(is_dir($sCurrentFilename)){
                customCacheClear($sCurrentFilename);
                rmdir($sCurrentFilename);
            }
            else{
                unlink($sCurrentFilename);
            }
            
        }
    }
