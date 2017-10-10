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
        "/logs/maildir/","/logs/smtplog/",$sBasePath
    );
    $sSMTPLogBasePath = preg_replace(
        "#^(.*)\.eml$#","$1.txt",
        $sSMTPLogBasePath
    );

    $arSMTPLog = [];
    if(file_exists($sSMTPLogBasePath)){
        $arSMTPLog = file($sSMTPLogBasePath);
    }

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
  <style>
    .headers td{
        padding: 3px 10px 3px 20px;
    }

    .mail-status{
        padding: 10px;
    }

  </style>

</head>
<body>
<? if(isset($_REQUEST["status"]) && $_REQUEST["status"]==1):?>
    <p class="bg-success mail-status">Письмо отправлено повторно</p>
<? elseif(isset($_REQUEST["status"]) && $_REQUEST["status"]==2):?>
    <p class="bg-danger mail-status">Ошибка повторной отправки письма</p>
<? endif ?>
<a href="request.frame.php?folder=<?= $sFolderPath?>&request=<?=
$sRequest    
?>" targer="_blank">
    Прямая ссылка на дамп этого запроса
</a>
<h3><?= $sRequest ?></h3>
<form action="/partners/mails/send.php" method="POST" class="send-mail">
<input name="emp" value="<?= $sFolderPath?>/<?= 
$sRequest?>" style="width:600px;" type="hidden">
<input type="submit" class="btn btn-primary" 
value="Послать письмо повторно">
</form>
<div id="accordion">

<? foreach($arParts as $nPartNo=>$arPart):?>
    <? if($nPartNo==1):?>
    <h3>Информация</h3>
        <div>
        <table class="headers">
        <? foreach($arPart["headers"] as $sHeader):?>
        <? 
            list($sName,$sValue) = explode(":",$sHeader);
            if(preg_match("#^.*=\?UTF\-8\?B\?(.+)=\?=(.*)$#", $sValue, $m))
                $sValue = htmlspecialchars(base64_decode($m[1]))
                    ." ".htmlspecialchars($m[2]);
            
        ?>
        <tr><th>
        <?= $sName?></th><td><?= $sValue?>
        </td></tr>
        <? endforeach ?>
        </table>
        </div>
    <? else: ?>
    <h3>Часть <?= ($nPartNo-1)?></h3>
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
    <? endif ?>
<? endforeach ?>
<? if($arSMTPLog):?>
    <h3>Журнал обмена с SMTP-сервером</h3>
        <div>
        <pre><?
            foreach($arSMTPLog as $sLine)
                echo htmlspecialchars(trim($sLine))."\n";
        ?></pre>
        </div>

<? endif ?>

</div>
</body>
</html>
<?

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

