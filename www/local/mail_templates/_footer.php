<?

if(trim( $orderInfo["PROPERTIES"]["RECEIVE_RULES"]["~VALUE"]["TEXT"]))
    $html .= '<p><b>Правила получения заказа</b><br>'.
    $orderInfo["PROPERTIES"]["RECEIVE_RULES"]["~VALUE"]["TEXT"]
    .'</p>';

if(trim($orderInfo["PROPERTIES"]["CANCEL_RULES"]["~VALUE"]["TEXT"]))
    $html .= '<p><b>Правила отмены заказа</b><br>'.
    $orderInfo["PROPERTIES"]["CANCEL_RULES"]["~VALUE"]["TEXT"]
    .'</p>';

$html .= 
'        <p>
            Данное письмо отправлено автоматически и не требует ответа.
            Дополнительную информацию Вы можете получить в разделе <a style="color:#08B0A8" href="http://ag.mos.ru/faq">«Помощь»</a> на сайте или обратившись в Службу поддержки пользователей
            <a style="color:#08B0A8" href="mailto:support_ag@mos.ru">support_ag@mos.ru</a>.
        </p>

    </div>

</body>
</html>
';
