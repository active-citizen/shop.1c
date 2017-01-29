<?
$html .='
        <p>
            '.
            (
                ($orderInfo["USER"]["NAME"] || $orderInfo["USER"]["LAST_NAME"])
                ?
                $orderInfo["USER"]["LAST_NAME"]." ".$orderInfo["USER"]["NAME"]
                :
                $orderInfo["USER"]["LOGIN"]
            )
            .', Благодарим за заказ в Магазине поощрений.
            Ваш заказ № '.
            (
                $orderInfo["ORDER"]["ADDITIONAL_INFO"]
            )
            .' от '.(
                $orderInfo["ORDER"]["DATE_INSERT"]
            ).' был изменён.
            Текущий статус заказа: '.(
                $orderInfo["STATUSES"][$orderInfo["ORDER"]["STATUS_ID"]]
            ).'        
        </p>
';
include("order_info.php");
