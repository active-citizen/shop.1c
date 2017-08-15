<?


if(trim($orderInfo["PROPERTIES"]["RECEIVE_RULES"]["VALUE"]["TEXT"]))
$html .='
        <p>
            <b>Правила получения:</b>
            <br>
        '.$orderInfo["PROPERTIES"]["RECEIVE_RULES"]["VALUE"]["TEXT"].'
';
