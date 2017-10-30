<?php
    
    function get_points($points){
        
        $points = preg_replace("#\s#","",$points);
        
        if($points%100==11){
            return 'баллов';
        }
        elseif($points%100==12){
            return 'баллов';
        }
        elseif($points%100==13){
            return 'баллов';
        }
        elseif($points%100==14){
            return 'баллов';
        }
        elseif($points%10==1){
            return 'балл';
        }
        elseif($points%10==3){
            return 'балла';
        }
        elseif($points%10==4){
            return 'балла';
        }
        else{
            return 'баллов';
        }
    }

    function get_days($points){
        
        $points = preg_replace("#\s#","",$points);
        
        if($points%100==11){
            return 'дней';
        }
        elseif($points%100==12){
            return 'дней';
        }
        elseif($points%100==13){
            return 'дней';
        }
        elseif($points%100==14){
            return 'дней';
        }
        elseif($points%10==1){
            return 'день';
        }
        elseif($points%10==3){
            return 'дня';
        }
        elseif($points%10==4){
            return 'дня';
        }
        elseif($points%10==2){
            return 'дня';
        }
        else{
            return 'дней';
        }
    }

    function get_date($date='',$rus = true){
        $mons = array(
            1=>"января",
            2=>"февраля",
            3=>"марта",
            4=>"апреля",
            5=>"мая",
            6=>"июня",
            7=>"июля",
            8=>"августа",
            9=>"сентября",
            10=>"октября",
            11=>"ноября",
            12=>"декабря",
        );

        if(!$date)$date = date("d.m.Y");
        $tmp = date_parse($date);
        
        if($rus)
            return 
                $tmp["day"]
                ." ".$mons[intval($tmp["month"])]
                ." ".$tmp["year"];
        else
            return 
                $tmp["day"]
                .".".$tmp["month"]
                .".".$tmp["year"];

    }

    function linkTruncate($text){
        if(preg_match(
            "#^(https?://)?(.*?)(/.*)?$#i",
            $text, $m
        )) 
            return $m[2].($m[3] && $m[3]!='/'?'/...':'');
        else
            return $text;
    }


function mb_wordwrap($str, $width = 75, $break = "\n", $cut = false) {
    $lines = explode($break, $str);
    foreach ($lines as &$line) {
        $line = rtrim($line);
        if (mb_strlen($line) <= $width)
            continue;
        $words = explode(' ', $line);
        $line = '';
        $actual = '';
        foreach ($words as $word) {
            if (mb_strlen($actual.$word) <= $width)
                $actual .= $word.' ';
            else {
                if ($actual != '')
                    $line .= rtrim($actual).$break;
                $actual = $word;
                if ($cut) {
                    while (mb_strlen($actual) > $width) {
                        $line .= mb_substr($actual, 0, $width).$break;
                        $actual = mb_substr($actual, $width);
                    }
                }
                $actual .= ' ';
            }
        }
        $line .= trim($actual);
    }
    return implode($break, $lines);
}

function drawWrappedText (
    &$im, 
    $nFontSize, 
    $nAngle,
    $nX, $nY,
    $objColor, 
    $sFont,
    $sText,
    $nWidth,
    $nLineHeight = 0.5
){
    $nMaxX = 0;
    $nMaxY = 0;
    
    $arText = explode("\n",mb_wordwrap($sText,$nWidth,"\n"));
    foreach($arText as $sLine){
        $arCoords = imagettftext (
            $im, 
            $nFontSize, $nAngle, 
            $nX, $nY+$nMax, 
            $objColor, 
            $sFont,
            html_entity_decode($sLine)
        );
        $nMax += ($arCoords[1]-$arCoords[5]+$nLineHeight*$nFontSize);
    }

    return $nMax;
}

function html2text($html){
    $html = str_replace("\n","",$html);
    $html = preg_replace("#<p.*?>#i","\n",$html);
    $html = preg_replace("#<br.*?>#i","\n",$html);
    $html = preg_replace("#<li.*?>#i","\n - ",$html);
    $html = preg_replace("#<ul.*?>#i","\n",$html);
    $html = preg_replace("#<ol.*?>#i","\n",$html);
    $html = str_replace("</div>","\n",$html);
    $html = str_replace("\t","    ",$html);
    $html = str_replace("\r","",$html);
    $html = strip_tags($html);
    return $html;
}

function getStatusAlias($sStatus){
    $arAliases = [
        "НОВЫЙ"     =>  "В обработке",
        "В РАБОТЕ"  =>  "Готово"
    ];

    $sResult = $sStatus;
    if(isset($arAliases[mb_strtoupper($sStatus)]))
        $sResult = $arAliases[mb_strtoupper($sStatus)];

    if(
       in_array($sStatus,$arAliases)
    )return false;
    
    
    return $sResult;
}
