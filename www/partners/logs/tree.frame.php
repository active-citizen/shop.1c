<?
    require($_SERVER["DOCUMENT_ROOT"].
            "/bitrix/modules/main/include/prolog_before.php");
    require("common.php");

    if(!$USER->isAdmin())die;

    $sFolderPath = $_REQUEST["folder"]?$_REQUEST["folder"]:'';
    if(
        preg_match("#\.\.#", $sFolderPath)
    )$sFolderPath = '';

    $sFilename = $sRootFolder.$sFolderPath;
    if(is_dir($sFilename)){
        $dd = opendir($sFilename);
        $arFolders = array();
        $arRequests = array();
        while($filename = readdir($dd)){
            if($filename == '..' || $filename == '.')continue;
            $sFullFilename = $sFilename."/".$filename;
            if(is_dir($sFullFilename)){
                $arFolders[$filename] = array();
            }
            elseif(
                preg_match("#^(\d+\-\d+\-\d+\-\d+\-\d+\-\d+\-\d+\.\d+)\..*$#", $filename ,$m)
            ){
                $sRequestName= $m[1];
                $arRequests[$sRequestName] = array(); 
                
            }
        }
        closedir($dd);
        ksort($arFolders);
        krsort($arRequests);

        $arPath = array();
        $tmp = explode("/",$sFolderPath);
        $sPath = '';
        foreach($tmp as $item){
            if(!trim($item))continue;
            $sPath .= '/'.$item;
            $arPath[$item] = $sPath;
        }
    }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>jQuery UI Accordion - Default functionality</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#accordion" ).accordion();
  } );
  </script>
</head>
<body>

    <? if($arPath):?>
    <ul class="tree-path">
        <? foreach($arPath as $name=>$value):?>
        <li>
            <a href="?folder=<?= $value?>"><?= $name?></a>
        </li>
        <? endforeach ?>
    </ul>
    <? endif ?>
    <? foreach($arFolders as $sFolder=>$arFolderInfo):?>
        <div class="tree-item folder">
            <a href="?folder=<?= $sFolderPath?>/<?= $sFolder?>">
               <?= $sFolder ?>
            </a>
        </div>
    <? endforeach?>
    <? foreach($arRequests as $sRequest=>$arRequestInfo):?>
        <div class="tree-item request">
            <a href="request.frame.php?folder=<?= $sFolderPath?>&request=<?= $sRequest?>"
            target="request_win">
               <?= $sRequest ?>
            </a>
        </div>
    <? endforeach?>

<style>
ul.tree-path{padding-left: 5px;}
ul.tree-path li{display:inline;padding-left:5px;}
a.selected{background-color:#CCF;}
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

