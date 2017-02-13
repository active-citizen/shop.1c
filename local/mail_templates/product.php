<?

if(trim($orderInfo["CATALOG"]["DETAIL_TEXT"]))
$html .='
        <p>
            <b>Описание товара:</b>
            <br>
        '.$orderInfo["CATALOG"]["DETAIL_TEXT"].'
';
