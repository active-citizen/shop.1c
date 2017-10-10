<?
    require($_SERVER["DOCUMENT_ROOT"].
            "/bitrix/modules/main/include/prolog_before.php");
    require("common.php");

    if(!$USER->isAdmin())die;

    if(!isset($_POST['emp'])){
        echo "eml-файл не указан";
        die;
    }

    if(preg_match("#\.\.#",$_POST["emp"])){
        echo "Переход в родительскую папку не разрешен";
        die;
    }

    $sFullPath = $sRootFolder.$_POST["emp"];

    if(!file_exists($sFullPath)){
        echo "eml-файл не найден";
        die;
    }

    if(is_dir($sFullPath)){
        echo "Это каталог";
        die;
    }

    $arLines = file($sFullPath);


    // Отделяем заголовки от тела и попутно узнаём адрес отправителя и subj
    $counter = 0;
    $sTo = '';
    $sSubject = '';
    $arHeaders = [];
    foreach($arLines as $nNum=>$sLine){
        $sLine = trim($sLine);
        if(!$sLine && $counter<2){$counter++;}
        if(!$sLine && $counter==2)break;
        $tmp = explode(":",$sLine);
        if(preg_match("#^to#i",trim($tmp[0]),$m)){
            $sTo = trim($tmp[1]);
            continue;
        }
        if(preg_match("#^Subject#i",trim($tmp[0]),$m)){
            $sSubject = trim($tmp[1]);
            continue;
        }
        if($sLine)$arHeaders[] = $sLine;
        unset($arLines[$nNum]);
    }

    $sBody = '';
    foreach($arLines as $sLine)
        $sBody .= trim($sLine)."\r\n";
    
    $sHeaders = '';
    foreach($arHeaders as $sLine)
        $sHeaders .= trim($sLine)."\r\n";

    if($sTo && $sSubject && $arLines && 
        custom_mail($sTo,$sSubject,$sBody,$sHeaders)){
        header("Location: ".$_SERVER["HTTP_REFERER"]."&status=1");
        die;
    }
    else{
        header("Location: ".$_SERVER["HTTP_REFERER"]."&status=2");
        die;
         
    }



