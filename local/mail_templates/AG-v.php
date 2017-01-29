<?
$html .= '
        <p>
            Антон Петрович,
            Ваш заказ № '.
            (
                $orderInfo["ORDER"]["ADDITIONAL_INFO"]
            )
            .' от '.(
                $orderInfo["ORDER"]["DATE_INSERT"]
            ).' был отменён.

        </p>
';
include("order_info.php");
