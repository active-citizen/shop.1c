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
    $sTo = $_POST["to"];
    $sSubject = '';
    $arHeaders = [];
    foreach($arLines as $nNum=>$sLine){
        $sLine = trim($sLine);
        if(!$sLine && $counter<2){$counter++;}
        if(!$sLine && $counter==2)break;
        $tmp = explode(":",$sLine);
        if(preg_match("#^to#i",trim($tmp[0]),$m)){
//            $sTo = trim($tmp[1]);
            unset($arLines[$nNum]);
            continue;
        }
        if(preg_match("#^Subject#i",trim($tmp[0]),$m)){
            $sSubject = trim($tmp[1]);
            unset($arLines[$nNum]);
            continue;
        }
        if($sLine)$arHeaders[] = $sLine;
        unset($arLines[$nNum]);
    }

    // Добавляем коммент службы поддержки
    $sComment = '';
    if(isset($_POST["comment"]) && trim($_POST["comment"])){
        $_POST["comment"] = str_replace("\n","<br>",$_POST["comment"]);
       
        $sComment =
            '<div style="background-color: #f2dede; padding: 10px; margin: 10px;">
                <h3 style="font-size: 16px; margin: 0px 0px 10px 0px;">
                    Комментарий службы поддержки
                </h3>
                '.$_POST["comment"].'
            </div>';
    }

    $sBody = '';
    foreach($arLines as $sLine)
        $sBody .= trim($sLine)."\r\n";
    $sBody = str_replace("</body>","$sComment</body>", $sBody);
    
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



