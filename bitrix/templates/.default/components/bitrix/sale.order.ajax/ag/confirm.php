<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
?>

<? if (!empty($arResult["ORDER"])): ?>
	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ORDER_SUC", array(
					"#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"],
					"#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
				))?>
				<? if (!empty($arResult['ORDER']["PAYMENT_ID"])): ?>
					<?=Loc::getMessage("SOA_PAYMENT_SUC", array(
						"#PAYMENT_ID#" => $arResult['PAYMENT'][$arResult['ORDER']["PAYMENT_ID"]]['ID']
					))?>
				<? endif ?>
				<br /><br />
				<?=Loc::getMessage("SOA_ORDER_SUC1", array("#LINK#" => $arParams["PATH_TO_PERSONAL"]))?>
			</td>
		</tr>
	</table>
	<?
	/** @var \Bitrix\Sale\Order $order */
	$order = \Bitrix\Sale\Order::load($arResult["ORDER"]["ID"]);
	if (!empty($order))
	{
		$paymentCollection = $order->getPaymentCollection();
		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if (!$payment->isPaid())
			{
				$service = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
				if (!empty($service))
				{
					if ($payment->isInner())
					{
						$service->initiatePay($payment);
					}
					else if (array_key_exists($payment->getPaymentSystemId(), $arResult['PAY_SYSTEM_LIST']))
					{
						$arPaySystem = $arResult['PAY_SYSTEM_LIST'][$payment->getPaymentSystemId()];
						?>
						<br /><br />

						<table class="sale_order_full_table">
							<tr>
								<td class="ps_logo">
									<div class="pay_name"><?=Loc::getMessage("SOA_PAY")?></div>
									<?=CFile::ShowImage($arPaySystem["LOGOTIP"], 100, 100, "border=0\" style=\"width:100px\"", "", false)?>
									<div class="paysystem_name"><?=$arPaySystem["NAME"]?></div>
									<br />
								</td>
							</tr>
							<? if (strlen($arPaySystem["ACTION_FILE"]) > 0): ?>
								<tr>
									<td>
										<? if ($arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
											<?
											$orderAccountNumber = urlencode(urlencode($order->getField('ACCOUNT_NUMBER')));
											$paymentAccountNumber = $payment->getField('ID');
											?>
											<script>
												window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=$orderAccountNumber?>&PAYMENT_ID=<?=$paymentAccountNumber?>');
											</script>
											<?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
											<? if (CSalePdf::isPdfAvailable() && $service->isAffordPdf()): ?>
												<br />
												<?=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
											<? endif ?>
										<? else: ?>
											<?
											try
											{
												$service->initiatePay($payment);
											}
											catch (Exception $e)
											{
												echo '<span style="color:red;">'.Loc::getMessage("SOA_ORDER_PS_ERROR").'</span>';
											}
											?>
										<? endif ?>
									</td>
								</tr>
							<? endif ?>
						</table>
						<?
					}
					else
					{
						echo '<span style="color:red;">'.Loc::getMessage("SOA_ORDER_PS_ERROR").'</span>';
					}
				}
				else
				{
					echo '<span style="color:red;">'.Loc::getMessage("SOA_ORDER_PS_ERROR").'</span>';
				}
			}
		}
	}
	?>
<? else: ?>
	<b><?=Loc::getMessage("SOA_ERROR_ORDER")?></b>
	<br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST", array("#ORDER_ID#" => $arResult["ACCOUNT_NUMBER"]))?>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
			</td>
		</tr>
	</table>
<? endif ?>