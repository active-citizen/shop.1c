<?
    require($_SERVER["DOCUMENT_ROOT"].
            "/bitrix/modules/main/include/prolog_before.php");
    require("common.php");

    if(!$USER->isAdmin())die;

    $sFolderPath = $_REQUEST["folder"]?$_REQUEST["folder"]:'';
    if(
        preg_match("#\.\.#", $sFolderPath)
    )$sFolderPath = '';

    $sMode = '';
    if(isset($_REQUEST['mode']) && $_REQUEST['mode'])
        $sMode = $_REQUEST['mode'];

    $sFilename = $sRootFolder.$sFolderPath;
    if(is_dir($sFilename)){
        $dd = opendir($sFilename);
        $arFolders = array();
        $arRequests = array();
        $arFiles = array();
        $arModes = array();
        while($filename = readdir($dd)){
            if($filename == '..' || $filename == '.')continue;
            $sFullFilename = $sFilename."/".$filename;
            if(is_dir($sFullFilename)){
                $arFolders[$filename] = array();
            }
            elseif(preg_match( "#^(.*\.eml)$#", $filename , $m)){
                $stat = stat($sFullFilename);
                $sRequestName= date("Y-m-d H:i:s",$stat["mtime"]);
                $arRequests[$sRequestName] = array(
                    "filename"=>$filename
                ); 
            }
        }
        closedir($dd);
        ksort($arFolders);
        ksort($arRequests);

        $arPath = array();
        $tmp = explode("/",$sFolderPath);
        $sPath = '';
        foreach($tmp as $item){
            if(!trim($item))continue;
            $sPath .= '/'.$item;
            $arPath[$item] = $sPath;
        }
    }

    if(isset($_REQUEST["query"]) && $_REQUEST["query"]){
        ob_start();
        passthru (
            $command = 
                'grep -C 100 -l -i -r --include="*.xml" --include="*.output.data" "'.
                escapeshellcmd ($_REQUEST["query"])."\" \"$sRootFolder\"", 
            $output
        );
        $sOutput = ob_get_contents();
        ob_end_clean();
        $arSearchRows = explode("\n",$sOutput);
        foreach($arSearchRows as $key=>$value)
            if(!trim($arSearchRows[$key]))unset($arSearchRows[$key]);
        
        $arSearchResult = array();
        foreach($arSearchRows as $key=>$value)
            $arSearchRows[$key] = str_replace($sRootFolder,"",$value);


        foreach($arSearchRows as $sFilename){
            $tmp = explode("/",$sFilename);
            $sRequest = array_pop($tmp);
            $tmp1 = explode("-",$sRequest);
            if(preg_match("#^(.*)\.([\w]+)\.data.*$#",$sRequest,$m)){
                $sRequest = $m[1];
                $sDirection = $m[2];
            }

            $arSearchResult[$sRequest] = array(
                "folder"=>implode("/",$tmp),
                "title"=> $tmp1[2].".".$tmp1[1].".".$tmp1[0]
                    ." ".$tmp1[3].".".$tmp1[4].".".$tmp1[5],
                "direction" =>$sDirection

            );
        }
        krsort($arSearchResult);
    }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>jQuery UI Accordion - Default functionality</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/local/assets/bootstrap/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#accordion" ).accordion();
  } );
  </script>
</head>
<body>

    <form>
        <input type="text" name="query" class="form-control" placeholder="Поиск"
        value="<?= htmlspecialchars($_REQUEST["query"])?>">
    </form>

    <? if(0 && $arPath):?>
    <ul class="tree-path">
        <? foreach($arPath as $name=>$value):?>
        <li>
            <a href="?folder=<?= $value?>"><span class="glyphicon
            glyphicon-folder-open"></span><?= $name?></a>
        </li>
        <? endforeach ?>
    </ul>
    <? endif ?>
    <? if(!$arSearchResult):?>
    <div class="refresh">
        <a href="?folder=<?= $sFolderPath?>&mode=<?= $sMode?>">
            <span class="glyphicon glyphicon-repeat"></span>
            Обновить
        </a>
    </div>
    <? endif ?>

    <h4><?= array_pop(explode("/",$sFolderPath))?></h4>

    <? if($arSearchResult):?>
    <h4>Результаты поиска по XML</h4>
    <? foreach($arSearchResult as $sRequest=>$arRequestInfo):?>
        <div class="tree-item request">
            <a href="request.frame.php?folder=<?= 
                $arRequestInfo["folder"]?>&request=<?= 
                $sRequest?>"
            target="request_win">
                <span class="glyphicon glyphicon-<?
                    if($arRequestInfo["direction"]=='input')
                        echo "file";
                    else
                        echo "question-sign"
                ?>"></span> 
                <?= $arRequestInfo["title"] ?>
            </a>
        </div>
    <? endforeach ?>
    <? endif ?>

    <? if($sFolderPath):?>
        <? 
            $tmp = explode("/",$sFolderPath);  
            array_pop($tmp);
            $sParentFolder = implode("/",$tmp);
        ?>
        <div class="tree-item folder">
            <a href="?folder=<?= $sParentFolder?>">
                <span class="glyphicon glyphicon-folder-open"></span>
                [...]
            </a>
        </div>
    <? endif ?>


    <? if(!$arSearchResult)foreach($arFolders as $sFolder=>$arFolderInfo):?>
        <div clams="tree-item folder">
            <a href="?folder=<?= $sFolderPath?>/<?= $sFolder?>">
                <span class="glyphicon glyphicon-folder-open"></span>
                <?= $sFolder ?>
            </a>
        </div>
    <? endforeach?>
    <? foreach($arRequests as $sRequest=>$arRequestInfo):?>
    <?
        $sRequestTitle = $sRequest;
    ?>
        <div class="tree-item request">
            <a href="request.frame.php?folder=<?= $sFolderPath?>&request=<?= 
                $arRequestInfo["filename"]?>"
            target="request_win">
                <?= $sRequestTitle ?>
                <? if(isset($arModes[$sRequest]["mode"])):?>
                    <?= $arModes[$sRequest]["mode"] ?>
                <? endif ?>
            </a>
        </div>
        <? if(isset($arFiles[$sRequest])):?>
            <? foreach($arFiles[$sRequest] as $sXMLFilename):?>
            <div class="tree-item-file">
                <?= $sXMLFilename?>
            </div>
            <? endforeach?>
        <? endif ?>
    <? endforeach?>

<style>
ul.tree-path{padding-left: 5px;}
ul.tree-path li{display:inline;padding-left:5px;}
ul.tree-path li a span{margin-right: 5px;}

ul.requests-filter{padding-left: 5px;}
ul.requests-filter li{display:inline;padding-left:5px;}
ul.requests-filter li a span{margin-right: 5px;}

.request{margin-left: 16px;}

h4{
    text-align:center;
}

div.tree-item-file{padding-left: 32px;}
div.folder span{padding-left: 16px;}
a.selected{background-color:#CCF;}
div.refresh{float: right;margin-right:10px;}
</style>

<script>
$('.tree-item a').click(function(){
    $('.tree-item a').removeClass('selected');
    $(this).addClass('selected');
});
</script>
</body>
</html>
<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

