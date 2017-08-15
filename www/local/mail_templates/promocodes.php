<?

    if($GLOBALS["promocodes"]){
        if(
            isset($GLOBALS["promocodes"]["ИмяПараметра1"])
            &&
            $GLOBALS["promocodes"]["ИмяПараметра1"]
            &&
            isset($GLOBALS["promocodes"]["ЗначениеПараметра1"])
            &&
            $GLOBALS["promocodes"]["ЗначениеПараметра1"]
        )$html .= '<tr><td colspan="3">' 
            .'<p><b style="color:#08B0A8; font-weight:bold">'
            .$GLOBALS["promocodes"]["ИмяПараметра1"]
            .': </b><span>'
            .$GLOBALS["promocodes"]["ЗначениеПараметра1"]
            .'</span> </p></td></tr>';

        if(
            isset($GLOBALS["promocodes"]["ИмяПараметра2"])
            &&
            $GLOBALS["promocodes"]["ИмяПараметра2"]
            &&
            isset($GLOBALS["promocodes"]["ЗначениеПараметра2"])
            &&
            $GLOBALS["promocodes"]["ЗначениеПараметра2"]
        )$html .= '<tr><td colspan="3">' 
            .'<p><b style="color:#08B0A8; font-weight:bold">'
            .$GLOBALS["promocodes"]["ИмяПараметра2"]
            .': </b><span>'
            .$GLOBALS["promocodes"]["ЗначениеПараметра2"]
            .'</span> </p></td></tr>';
    }
