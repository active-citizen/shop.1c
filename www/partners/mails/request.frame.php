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
    $sSMTPLogBasePath = str_replace(
        "/logs/maildir/","./logs/smtplog/",$sBasePath
    );
    $sSMTPLogBasePath = preg_replace(
        "#^(.*)\.eml$#","$1.txt",
        $sSMTPLogBasePath
    );
    echo $sSMTPLogBasePath;

    $arSMTPLog = [];
    if(file_exists($sSMTPLogBasePath)){
        $arSMTPLog = file($sSMTPLogBasePath);
    }
    echo "<pre>";
    print_r($arSMTPLog);
    die;

    $sRawMail = '';
    $fd = fopen($sBasePath,"r");
    $counter = 0;
    while(!feof($fd)){
        $sRawMail .= fgets($fd);
    }
    fclose($fd);

    $arParts = mailParse($sRawMail); 
    $nCounter++;
    function mailParse($sRawMail){
        $arLines = explode("\n", $sRawMail);

        foreach($arLines as $key=>$sLine){
            $arLines[$key] = trim($sLine);
        }
        $arLines = array_merge(array("----headpart"),$arLines);
        
        $arParts = [];
        $nPartNo = 0;
        $nCounter==0;
        foreach($arLines as $sLine){
            $sLine = trim($sLine);
            if(preg_match("#^\-{3,}.*#", $sLine)){
                $nPartNo++;
                $nCounter=0;
                continue;
            }
            if(!isset($arParts[$nPartNo])){
                $arParts[$nPartNo] = [
                    "content-type"=>"",
                    "headers"   =>  [],
                    "body"      =>  [],
                ];
            }
            if(!$sLine && $nCounter==0){
                $nCounter++;
                continue;
            }
            if($nCounter==1){
                $arParts[$nPartNo]["body"][] = $sLine;
                continue;
            }
            $arParts[$nPartNo]["headers"][] = $sLine;
            if(
                !$nCounter 
                && preg_match("#^content-type\s*:\s*(.*)$#i",$sLine,$m)
            )$arParts[$nPartNo]["content-type"] = $m[1];

        }

        return $arParts;
    }

    

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
<a href="request.frame.php?folder=<?= $sFolderPath?>&request=<?=
$sRequest    
?>" targer="_blank">
    Прямая ссылка на дамп этого запроса
</a>
<h3><?= $sRequest ?></h3>

<div id="accordion">

<? foreach($arParts as $nPartNo=>$arPart):?>
    <h3>Часть <?= $nPartNo?></h3>
        <div>
            <pre><?= implode("\n",$arPart["headers"]) ?></pre> 
        <? if(preg_match("#html#",$arPart["content-type"])):?>
            <?= implode("\n",$arPart["body"])?>
        <? elseif(preg_match("#image#",$arPart["content-type"])):
            $tmp = explode(";", $arPart["content-type"]);
            $sCType = $tmp[0]; 
        ?>
            <img src="data:<?= $sCType?>;base64,<?=
            implode("",$arPart["body"])?>">
        <? endif ?>
        </div>
<? endforeach ?>

</div>
</body>
</html>
<?

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

