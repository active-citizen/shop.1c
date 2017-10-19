<?

$html .='
        <p>
            <b>Информация о заказе:</b>
            <br>
            Заказ № '.
            (
                $orderInfo["ORDER"]["ADDITIONAL_INFO"]
            )
            .' от '.(
                $orderInfo["ORDER"]["DATE_INSERT"]
            ).'            <br>
            Статус заказа: '.(
                $orderInfo["STATUSES"][$orderInfo["ORDER"]["STATUS_ID"]]["NAME"]
            ).'        
            <table style="border-spacing: 0px;border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #000000">
                    <td>Наименование</td>
                    <td style="width:150px; text-align: right;">Сумма баллов</td>
                    <td style="width:150px; text-align: right;">Количество</td>
                    <td>Срок действия</td>
                </tr>
                <tr>
                    <td style="color:#08B0A8;font-weight:bold">'.(
                        /*Набор для записей с символикой проекта зеленый*/
                        $orderInfo["CATALOG"]['NAME']
                    ).', '.(
                        $orderInfo["PROPERTIES"]['QUANT']['VALUE']
                    ).'</td>
                    <td style="width:100px; text-align: right; color:#08B0A8;font-weight:bold">'.(
                        number_format($orderInfo["BASKET"]['QUANTITY']*$orderInfo["BASKET"]['PRICE'],2,',',' ')
                    ).'</td>
                    <td style="width:100px; text-align: right; color:#08B0A8;font-weight:bold">'.(
                        $orderInfo["BASKET"]['QUANTITY']
                    ).'</td>
                    <td style="text-align: right;color:#08B0A8;font-weight:bold">'.(
                        $orderInfo["CATALOG"]['EXPIRES']
                    ).'</td>
                </tr>';
        include("promocodes.php");
        $html .= '
            </table>'
        .(
            isset($arFields["SUPPORT_COMMENT"])
            &&
            trim($arFields["SUPPORT_COMMENT"])
            ?
            '<div style="margin:20px;color: red;">Комментарий: <span style="font-weight:bold;">'
            .$arFields["SUPPORT_COMMENT"]
            .'</span></div>'
            :
            ""
        )
        .'</p> ';
