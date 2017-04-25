<?php

    function customCache($lifetime = 1){
        $sCacheDir = $_SERVER["DOCUMENT_ROOT"]."/upload/custom_cache";
        $sHash = md5($_SERVER["REQUEST_URI"]);
        
        $sHashFilename = $sCacheDir."/"
            .mb_substr($sHash, 0, 1)."/"
            .mb_substr($sHash, 1, 1)."/"
            .mb_substr($sHash, 2, 1)."/"
            .$sHash.".html";

        define("CUSTOM_CACHE_HASH_FILENAME", $sHashFilename);
        define("CUSTOM_CACHE_DIR", $sCacheDir);

        $bRefresh = true;
        if(file_exists($sHashFilename)){
            $stat = stat($sHashFilename);
            if(time() - $stat["mtime"]<$lifetime)$bRefresh = false;

        }
        if($bRefresh){
            ob_start("customCacheStore");
        }
        else{
            header("Content-Encoding:gzip");
            echo file_get_contents($sHashFilename);
            die;
        }

    }

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
