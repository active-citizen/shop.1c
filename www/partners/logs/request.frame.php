<?
    require($_SERVER["DOCUMENT_ROOT"].
            "/bitrix/modules/main/include/prolog_before.php");
    require("common.php");

    if(!$USER->isAdmin())die;

    $sFolderPath = $_REQUEST["folder"]?$_REQUEST["folder"]:'';
    $sRequest = $_REQUEST["request"]?$_REQUEST["request"]:'';
    if(
        preg_match("#\.\.#", $sFolderPath)
    )$sFolderPath = '';

    if(
        preg_match("#\.\.#",$sRequest)
    )$sRequest = '';

    $sBasePath = $sRootFolder.$sFolderPath."/".$sRequest;

    $sInputHeadersFilename = $sBasePath.".input.headers";
    $arInputHeaders = array();
    if(file_exists($sInputHeadersFilename)){
        $arInputHeaders = file($sInputHeadersFilename);    
    }

    $sOutputHeadersFilename = $sBasePath.".output.headers";
    $arOutputHeaders = array();
    if(file_exists($sOutputHeadersFilename)){
        $arOutputHeaders = file($sOutputHeadersFilename);    
    }

    $sOutputDataFilename = $sBasePath.".output.data";
    $arOutputData = "";
    if(file_exists($sOutputDataFilename)){
        
        $arOutputData = file_get_contents($sOutputDataFilename);    

        if(preg_match("#mode=query#", $arInputHeaders[0]))
            $arOutputData = mb_convert_encoding($arOutputData,"utf-8","cp1251");

    }


    $arFiles = array();
    $dd = opendir($sRootFolder.$sFolderPath);
    while($filename = readdir($dd)){
        if(strpos($filename,$sRequest)===false)continue;
        if(!preg_match("#.*\.([\w\d\-\_]+\.xml)$#",$filename,$m))continue;
        $arFiles[$m[1]] = $sRootFolder.$sFolderPath."/".$filename;
    }
    closedir($dd);


?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $sRequest?>::дамп обмена</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/local/assets/bootstrap/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#accordion" ).accordion({
        heightStyle: "content",
        collapsible: true
    });
  } );
  </script>
</head>
<body>
<a href="/partners/logs/request.frame.php?folder=<?= $sFolderPath?>&request=<?=
$sRequest    
?>" targer="_blank">
    Прямая ссылка на дамп этого запроса
</a>
<h3><?= $sRequest ?></h3>

<div id="accordion">


<? if($arInputHeaders):?>
    <h3>Заголовки запроса</h3>
        <div>
            <pre>
<? foreach($arInputHeaders as $sHeaderLine):?>
<?= $sHeaderLine?>
<? endforeach ?>
            </pre>
        </div>
<? endif ?>

<? if($arInputData):?>
    <h3>Тело запроса</h3>
        <div>
            <pre>
<?= $arInputData?>
            </pre>
        </div>
<? endif ?>



<? if($arOutputHeaders):?>
    <h3>Заголовки ответа</h3>
        <div>
            <pre>
<? foreach($arOutputHeaders as $sHeaderLine):?>
<?= $sHeaderLine?>
<? endforeach ?>
            </pre>
        </div>
<? endif ?>


<? if($arOutputData):?>
    <h3>Тело ответа</h3>
        <div>
            <pre>
<?= htmlspecialchars($arOutputData)?>
            </pre>
        </div>
<? endif ?>

<? if($arFiles):?>
    <? foreach($arFiles as $sName=>$sPath):?>
    <h3><?= $sName?></h3>
        <div>
            <pre>
<?= htmlspecialchars(file_get_contents($sPath))?>
            </pre>
        </div>
    <? endforeach ?>
<? endif ?>

</div>
</body>
</html>
<?

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

