<?

if(trim($orderInfo["PROPERTIES"]["CANCEL_RULES"]["VALUE"]["TEXT"]))
$html .='
        <p>
            <b>Правила отмены:</b>
            <br>
    '.$orderInfo["PROPERTIES"]["CANCEL_RULES"]["VALUE"]["TEXT"].'
';
