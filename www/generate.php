<?
die;
    $sBasePath = $_SERVER["DOCUMENT_ROOT"];
    $sUrl = "/upload/q/1218";
    $sOriginalPath = "/upload/q/original_files";
    

    $dd = opendir($sBasePath.$sOriginalPath);
    while($sFilename = readdir($dd)){
        if($sFilename =='.' || $sFilename=='..')continue;
        $newFilename = crc32(time().rand());
        $ext = 'bin';
        if(preg_match("#^.*\.(.*?)$#",$sFilename,$m))$ext = $m[1];
        $sNewPath = $sUrl."/".$newFilename.".$ext";
        copy(
            $from = $sBasePath.$sOriginalPath."/$sFilename",
            $to = $sBasePath.$sNewPath
        );
//        echo "copy from $from to $to<br/>";
        echo "http://".$_SERVER["HTTP_HOST"].$sNewPath."<br/>";
    }
    closedir($dd);

