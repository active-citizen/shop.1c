<!DOCTYPE html>
<html>
    
    <link href="/local/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <title></title>
</html>
<body>
<?
    $sStartFolder = realpath(__DIR__)."/CAGShop";


    $arFiles = getTree($sStartFolder);

    $arSummary = getSummary($arFiles);
    echo "<pre>";
//    print_r($arSummary);
//    print_r($arFiles);
    echo "</pre>";

    echo "<h1>Покрытие тестами ".$arSummary["tested_percent"]."%</h1>";
    createTable($arFiles);

?>
</body>
</html>
<?
    function getTree($sPath){
        $arResult = [];
        $dd = opendir($sPath);
        while($sFile = readdir($dd)){
            if($sFile=='.' || $sFile=='..')continue;
            if(is_dir($sPath."/".$sFile))
                $arResult = array_merge(
                    $arResult
                    ,getTree($sPath."/".$sFile)
                );
            elseif(preg_match("#\.class\.php$#",$sFile))
                $arResult[$sPath."/".$sFile]=[
                    "namespace"=>getNamespace($sPath."/".$sFile),
                    "className"=>preg_replace("#^([\w\d]+)\.class\.php$#","$1",$sFile),
                    "methods"=>getMethods($sPath."/".$sFile),
                ];

        }
        closedir($dd);
        return $arResult;
    }

    function getNamespace($sFilename){
        $sData = file_get_contents($sFilename);
        $sData = str_replace("\n","{{break}}",$sData);
        if(preg_match("#namespace\s+([\w\d]+)\;#i",$sData,$m))
            return $m[1];
    }

    function getMethods($sFilename){
        $sData = file_get_contents($sFilename);
        $sData = str_replace("\n","{{break}}",$sData);
        if(!preg_match_all("#\{\{break\}\}\s*([\w\s]+)?\s*function\s+([\d\w]+)\s*\(#",$sData,$m))
            return [];

        $arResult = [];
        foreach($m[2] as $k=>$v){
            $arResult[trim($v)]= [
                "type"=>
                    trim($m[1][$k])
                    ?
                    trim($m[1][$k])
                    :
                    "public"
                ,"tested"=>
                    trim($m[1][$k])!='private'
                    ?
                    isTested($sFilename,trim($v))
                    :
                    false
            ];
        }
        
        return $arResult;
    }

    function isTested($sFilename,$sMethodName){
        if($sMethodName=='__construct')return true;
        $sDir = dirname($sFilename);
        $sClassName = preg_replace("#.*\/([\w\d]+)\.class\.php$#","$1",$sFilename);
        $sTestFilename =
            $sDir."/tests/".$sClassName."-".$sMethodName.".Test.php";
        return file_exists($sTestFilename);
    }


    function getSummary($arFiles){
        $nTotal = 0;
        $nTested = 0;
        foreach($arFiles as $arFile){
            foreach($arFile["methods"] as $arMethod){
                if($arMethod["type"]=="private")continue;
                $nTotal++;
                if($arMethod["tested"]) $nTested++;
            }
        }
        return [
            "tested_percent"=>round(100*($nTested/$nTotal),2)
        ];
    }

    function createTable($arFiles){
        echo
        '<style>table{width:80% !important;margin: 10px auto 10px auto;}</style>'
        .'<table class="table table-bordered"><tr><th>N</th><th>Namespace</th><th>Class</th><th>Method</th><th>is tested</th></tr>';
	$n = 0;
        foreach($arFiles as $arFile){
            foreach($arFile["methods"] as $sMethod=>$arMethod){
                if(trim($arMethod["type"])=='private')continue;
		$n++;
                echo 
                    '<tr'.($arMethod["tested"]?' class="success"':"").'><td>'.$n.'</td><td>'.$arFile["namespace"].'</td>'
                    .'<td>'.$arFile["className"].'</td>'
                    .'<td>'.$sMethod.'</td>'
                    .'<td>'.($arMethod["tested"]?"yes":"no").'</td></tr>';
                ;
            }
        }
        echo '</table>';
 
    }
