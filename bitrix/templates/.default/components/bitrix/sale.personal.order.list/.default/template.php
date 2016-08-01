<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(!empty($arResult['ERRORS']['FATAL'])):?>

	<?foreach($arResult['ERRORS']['FATAL'] as $error):?>
		<?=ShowError($error)?>
	<?endforeach?>

	<?$component = $this->__component;?>
	<?if($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED])):?>
		<?$APPLICATION->AuthForm('', false, false, 'N', false);?>
	<?endif?>

<?else:?>

	<?if(!empty($arResult['ERRORS']['NONFATAL'])):?>

		<?foreach($arResult['ERRORS']['NONFATAL'] as $error):?>
			<?=ShowError($error)?>
		<?endforeach?>

	<?endif?>

	<div class="bx_my_order_switch">

		<?$nothing = !isset($_REQUEST["filter_history"]) && !isset($_REQUEST["show_all"]);?>

			<a class="bx_mo_link <?if(isset($_GET['show_all'])):?>active<?endif?>" href="<?=$arResult["CURRENT_PAGE"]?>?show_all=Y"><?=GetMessage('SPOL_ORDERS_ALL')?></a>

			<a class="bx_mo_link <?if(isset($_GET['filter_history']) && $_GET['filter_history']=='N'):?>active<?endif?>" href="<?=$arResult["CURRENT_PAGE"]?>?filter_history=N"><?=GetMessage('SPOL_CUR_ORDERS')?></a>

			<a class="bx_mo_link <?if(isset($_GET['filter_history']) && $_GET['filter_history']=='Y'):?>active<?endif?>" href="<?=$arResult["CURRENT_PAGE"]?>?filter_history=Y"><?=GetMessage('SPOL_ORDERS_HISTORY')?></a>

	</div>

	<?if(!empty($arResult['ORDERS'])):?>

		<?foreach($arResult["ORDER_BY_STATUS"] as $key => $group):?>

			<?foreach($group as $k => $order):?>

				<?if(!$k):?>

					<div class="bx_my_order_status_desc">

						<h2><?=GetMessage("SPOL_STATUS")?> "<?=$arResult["INFO"]["STATUS"][$key]["NAME"] ?>"</h2>
						<div class="bx_mos_desc"><?=$arResult["INFO"]["STATUS"][$key]["DESCRIPTION"] ?></div>

					</div>

				<?endif?>

				<div class="bx_my_order">
					
					<table class="bx_my_order_table">
						<thead>
							<tr>
								<td>
									<?=GetMessage('SPOL_ORDER')?> <?=GetMessage('SPOL_NUM_SIGN')?><?=$order["ORDER"]["ACCOUNT_NUMBER"]?>
									<?if(strlen($order["ORDER"]["DATE_INSERT_FORMATED"])):?>
										<?=GetMessage('SPOL_FROM')?> <?=$order["ORDER"]["DATE_INSERT_FORMATED"];?>
									<?endif?>
								</td>
								<td style="text-align: right;">
									<a href="<?=$order["ORDER"]["URL_TO_DETAIL"]?>"><?=GetMessage('SPOL_ORDER_DETAIL')?></a>
								</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<strong><?=GetMessage('SPOL_PAY_SUM')?>:</strong> <?=$order["ORDER"]["FORMATED_PRICE"]?> <br />

                                    <? // PAY SYSTEM ?>
									<? $paySystemList = array();?>
									<?foreach($order["PAYMENT"] as $payment):?>
										<?$paySystemList[] = $arResult['INFO']['PAY_SYSTEM'][$payment['PAY_SYSTEM_ID']]['NAME'];?>
									<?endforeach;?>

									<?if(!empty($deliveryServiceList)):?>
										<strong><?=GetMessage('SPOL_DELIVERY')?>:</strong> <?=implode(', ', $deliveryServiceList)?> <br />
									<?endif?>

									<strong><?=GetMessage('SPOL_BASKET')?>:</strong>
									<ul class="bx_item_list">

										<?foreach ($order["BASKET_ITEMS"] as $item):?>

											<li>
												<?if(strlen($item["DETAIL_PAGE_URL"])):?>
													<a href="<?=$item["DETAIL_PAGE_URL"]?>" target="_blank">
												<?endif?>
													<?=$item['NAME']?>
												<?if(strlen($item["DETAIL_PAGE_URL"])):?>
													</a> 
												<?endif?>
												<nobr>&nbsp;&mdash; <?=$item['QUANTITY']?> <?=(isset($item["MEASURE_NAME"]) ? $item["MEASURE_NAME"] : GetMessage('SPOL_SHT'))?></nobr>
											</li>

										<?endforeach?>

									</ul>

								</td>
								<td>
									<?=$order["ORDER"]["DATE_STATUS_FORMATED"];?>
									<div class="bx_my_order_status <?=$arResult["INFO"]["STATUS"][$key]['COLOR']?><?/*yellow*/ /*red*/ /*green*/ /*gray*/?>"><?=$arResult["INFO"]["STATUS"][$key]["NAME"]?></div>
                                        

									<?if($order["CANCEL_ABILITY"] && $order["ORDER"]["CANCELED"] != "Y"):?>
									<!-- 
										<a href="<?=$order["ORDER"]["URL_TO_CANCEL"]?>" style="min-width:140px" 
                                        class="bx_big bx_bt_button_type_2 bx_cart bx_order_action"><?=GetMessage('SPOL_CANCEL_ORDER')?></a>
                                    -->
                                        <? if($order["ORDER"]["STATUS_ID"]=='N'):?>
                                        <a href="#" onclick="return order_cancel(<?=$order["ORDER"]["ID"]?>,this);" style="min-width:140px" 
                                        id="ag-order-<?=$order["ORDER"]["ID"]?>" class="bx_big bx_bt_button_type_2 bx_cart bx_order_action ag-cancel-button">
                                            <?=GetMessage('SPOL_CANCEL_ORDER')?>
                                        </a>
                                        <a href="#" onclick="return false;" id="ag-cancel-loader-<?=$order["ORDER"]["ID"]?>" class="ag-button-loader"
                                        style="display:none;"
                                        >
                                            Загрузка...
                                        </a>
                                        <?endif?>
									<?endif?>


									<!-- <a href="<?=$order["ORDER"]["URL_TO_COPY"]?>" style="min-width:140px" class="bx_big bx_bt_button_type_2 bx_cart bx_order_action"><?=GetMessage('SPOL_REPEAT_ORDER')?></a> -->
								</td>
							</tr>
						</tbody>
					</table>

				</div>

			<?endforeach?>

		<?endforeach?>

		<?if(strlen($arResult['NAV_STRING'])):?>
			<?=$arResult['NAV_STRING']?>
		<?endif?>

	<?else:?>
		<?=GetMessage('SPOL_NO_ORDERS')?>
	<?endif?>

<?endif?>
