<?
    $html .= '<ul>';
    if(
        isset($GLOBALS["promocodes"]["ИмяПараметра1"])
        &&
        $GLOBALS["promocodes"]["ИмяПараметра1"]
        &&
        isset($GLOBALS["promocodes"]["ЗначениеПараметра1"])
        &&
        $GLOBALS["promocodes"]["ЗначениеПараметра1"]
    )$html .= '<li>'
        .$GLOBALS["promocodes"]["ИмяПараметра1"]
        ." : "
        .$GLOBALS["promocodes"]["ЗначениеПараметра1"]
        .'</li>';

    $html .= '</ul>';
