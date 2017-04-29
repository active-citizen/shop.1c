<?

$html .= '
        <p>
            '.$orderInfo["USER"]["LAST_NAME"].' '.$orderInfo["USER"]["NAME"].',
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
