<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var $component SaleOrderAjax
 */

if (empty($arParams['TEMPLATE_THEME']))
{
	$arParams['TEMPLATE_THEME'] = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix.eshop') ? 'site' : 'blue';
}

if ($arParams['TEMPLATE_THEME'] == 'site')
{
	$templateId = \Bitrix\Main\Config\Option::get("main", "wizard_template_id", "eshop_bootstrap", SITE_ID);
	$templateId = preg_match("/^eshop_adapt/", $templateId) ? "eshop_adapt" : $templateId;
	$arParams['TEMPLATE_THEME'] = \Bitrix\Main\Config\Option::get('main', 'wizard_' . $templateId . '_theme_id', 'blue', SITE_ID);
}

if (!empty($arParams['TEMPLATE_THEME']))
{
	if (!is_file($_SERVER['DOCUMENT_ROOT'].'/bitrix/css/main/themes/'.$arParams['TEMPLATE_THEME'].'/style.css'))
		$arParams['TEMPLATE_THEME'] = 'blue';
}

$arParams["ALLOW_USER_PROFILES"] = $arParams["ALLOW_USER_PROFILES"] == "Y" ? "Y" : "N";
$arParams["SKIP_USELESS_BLOCK"] = $arParams["SKIP_USELESS_BLOCK"] == "N" ? "N" : "Y";
if (!isset($arParams['SHOW_ORDER_BUTTON']))
	$arParams['SHOW_ORDER_BUTTON'] = 'final_step';
$arParams["SHOW_TOTAL_ORDER_BUTTON"] = $arParams["SHOW_TOTAL_ORDER_BUTTON"] == "Y" ? "Y" : "N";
$arParams['SHOW_PAY_SYSTEM_LIST_NAMES'] = $arParams['SHOW_PAY_SYSTEM_LIST_NAMES'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_PAY_SYSTEM_INFO_NAME'] = $arParams['SHOW_PAY_SYSTEM_INFO_NAME'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_DELIVERY_LIST_NAMES'] = $arParams['SHOW_DELIVERY_LIST_NAMES'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_DELIVERY_INFO_NAME'] = $arParams['SHOW_DELIVERY_INFO_NAME'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_DELIVERY_PARENT_NAMES'] = $arParams['SHOW_DELIVERY_PARENT_NAMES'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_STORES_IMAGES'] = $arParams['SHOW_STORES_IMAGES'] == 'N' ? 'N' : 'Y';
if (!isset($arParams['BASKET_POSITION']))
	$arParams['BASKET_POSITION'] = 'after';
$arParams['SHOW_BASKET_HEADERS'] = $arParams['SHOW_BASKET_HEADERS'] == 'Y' ? 'Y' : 'N';
$arParams['DELIVERY_FADE_EXTRA_SERVICES'] = $arParams['DELIVERY_FADE_EXTRA_SERVICES'] == 'Y' ? 'Y' : 'N';
$arParams['SHOW_COUPONS_BASKET'] = $arParams['SHOW_COUPONS_BASKET'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_COUPONS_DELIVERY'] = $arParams['SHOW_COUPONS_DELIVERY'] == 'N' ? 'N' : 'Y';
$arParams['SHOW_COUPONS_PAY_SYSTEM'] = $arParams['SHOW_COUPONS_PAY_SYSTEM'] == 'Y' ? 'Y' : 'N';
$arParams['SHOW_NEAREST_PICKUP'] = $arParams['SHOW_NEAREST_PICKUP'] == 'Y' ? 'Y' : 'N';
$arParams['DELIVERIES_PER_PAGE'] = intval($arParams['DELIVERIES_PER_PAGE']);
$arParams['PAY_SYSTEMS_PER_PAGE'] = intval($arParams['PAY_SYSTEMS_PER_PAGE']);
$arParams['PICKUPS_PER_PAGE'] = intval($arParams['PICKUPS_PER_PAGE']);
$arParams['SHOW_MAP_IN_PROPS'] = $arParams['SHOW_MAP_IN_PROPS'] == 'Y' ? 'Y' : 'N';
$arParams['USE_YM_GOALS'] = $arParams['USE_YM_GOALS'] == 'Y' ? 'Y' : 'N';

if ($arParams["USE_CUSTOM_MAIN_MESSAGES"] != "Y")
{
	$arParams['MESS_AUTH_BLOCK_NAME'] = Loc::getMessage("AUTH_BLOCK_NAME_DEFAULT");
	$arParams['MESS_REG_BLOCK_NAME'] = Loc::getMessage("REG_BLOCK_NAME_DEFAULT");
	$arParams['MESS_BASKET_BLOCK_NAME'] = Loc::getMessage("BASKET_BLOCK_NAME_DEFAULT");
	$arParams['MESS_REGION_BLOCK_NAME'] = Loc::getMessage("REGION_BLOCK_NAME_DEFAULT");
	$arParams['MESS_PAYMENT_BLOCK_NAME'] = Loc::getMessage("PAYMENT_BLOCK_NAME_DEFAULT");
	$arParams['MESS_DELIVERY_BLOCK_NAME'] = Loc::getMessage("DELIVERY_BLOCK_NAME_DEFAULT");
	$arParams['MESS_BUYER_BLOCK_NAME'] = Loc::getMessage("BUYER_BLOCK_NAME_DEFAULT");
	$arParams['MESS_BACK'] = Loc::getMessage("BACK_DEFAULT");
	$arParams['MESS_FURTHER'] = Loc::getMessage("FURTHER_DEFAULT");
	$arParams['MESS_EDIT'] = Loc::getMessage("EDIT_DEFAULT");
	$arParams['MESS_ORDER'] = Loc::getMessage("ORDER_DEFAULT");
	$arParams['MESS_PRICE'] = Loc::getMessage("PRICE_DEFAULT");
	$arParams['MESS_PERIOD'] = Loc::getMessage("PERIOD_DEFAULT");
	$arParams['MESS_NAV_BACK'] = Loc::getMessage("NAV_BACK_DEFAULT");
	$arParams['MESS_NAV_FORWARD'] = Loc::getMessage("NAV_FORWARD_DEFAULT");
}

if ($arParams["USE_CUSTOM_ADDITIONAL_MESSAGES"] != "Y")
{
	$arParams['MESS_REGISTRATION_REFERENCE'] = Loc::getMessage("REGISTRATION_REFERENCE_DEFAULT");
	$arParams['MESS_AUTH_REFERENCE_1'] = Loc::getMessage("AUTH_REFERENCE_1_DEFAULT");
	$arParams['MESS_AUTH_REFERENCE_2'] = Loc::getMessage("AUTH_REFERENCE_2_DEFAULT");
	$arParams['MESS_AUTH_REFERENCE_3'] = Loc::getMessage("AUTH_REFERENCE_3_DEFAULT");
	$arParams['MESS_ADDITIONAL_PROPS'] = Loc::getMessage("ADDITIONAL_PROPS_DEFAULT");
	$arParams['MESS_USE_COUPON'] = Loc::getMessage("USE_COUPON_DEFAULT");
	$arParams['MESS_COUPON'] = Loc::getMessage("COUPON_DEFAULT");
	$arParams['MESS_PERSON_TYPE'] = Loc::getMessage("PERSON_TYPE_DEFAULT");
	$arParams['MESS_SELECT_PROFILE'] = Loc::getMessage("SELECT_PROFILE_DEFAULT");
	$arParams['MESS_REGION_REFERENCE'] = Loc::getMessage("REGION_REFERENCE_DEFAULT");
	$arParams['MESS_PICKUP_LIST'] = Loc::getMessage("PICKUP_LIST_DEFAULT");
	$arParams['MESS_NEAREST_PICKUP_LIST'] = Loc::getMessage("NEAREST_PICKUP_LIST_DEFAULT");
	$arParams['MESS_SELECT_PICKUP'] = Loc::getMessage("SELECT_PICKUP_DEFAULT");
	$arParams['MESS_INNER_PS_BALANCE'] = Loc::getMessage("INNER_PS_BALANCE_DEFAULT");
	$arParams['MESS_ORDER_DESC'] = Loc::getMessage("ORDER_DESC_DEFAULT");
}

if ($arParams["USE_CUSTOM_ERROR_MESSAGES"] != "Y")
{
	$arParams['MESS_DELIVERY_CALC_ERROR_TITLE'] = Loc::getMessage("DELIVERY_CALC_ERROR_TITLE_DEFAULT");
	$arParams['MESS_DELIVERY_CALC_ERROR_TEXT'] = Loc::getMessage("DELIVERY_CALC_ERROR_TEXT_DEFAULT");
}

$scheme = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http';
switch (LANGUAGE_ID)
{
	case 'ru':
		$locale = 'ru-RU'; break;
	case 'ua':
		$locale = 'ru-UA'; break;
	case 'tk':
		$locale = 'tr-TR'; break;
	default:
		$locale = 'en-US'; break;
}

$this->addExternalCss('/bitrix/css/main/bootstrap.css');
$this->addExternalCss('/bitrix/components/bitrix/sale.location.selector.search/templates/.default/style.css');
$this->addExternalCss('/bitrix/components/bitrix/sale.location.selector.steps/templates/.default/style.css');
$APPLICATION->SetAdditionalCSS('/bitrix/css/main/themes/'.$arParams['TEMPLATE_THEME'].'/style.css', true);
$APPLICATION->SetAdditionalCSS($templateFolder.'/style.css', true);
$this->addExternalJs($templateFolder.'/order_ajax.js');
\Bitrix\Sale\PropertyValueCollection::initJs();
$this->addExternalJs($templateFolder.'/script.js');
$this->addExternalJs($scheme.'://api-maps.yandex.ru/2.1.34/?load=package.full&lang='.$locale);
?>
<NOSCRIPT>
	<div style="color:red"><?=Loc::getMessage("SOA_NO_JS")?></div>
</NOSCRIPT>
<?
$context = Bitrix\Main\Application::getInstance()->getContext();
if (strlen($context->getRequest()->get('ORDER_ID')) > 0):
	include($context->getServer()->getDocumentRoot().$templateFolder."/confirm.php");
elseif ($arParams["DISABLE_BASKET_REDIRECT"] == 'Y' && $arResult["SHOW_EMPTY_BASKET"]):
	include($context->getServer()->getDocumentRoot().$templateFolder."/empty.php");
else:
	$hideDelivery = empty($arResult["DELIVERY"]);
?>
<form action="<?=$APPLICATION->GetCurPage();?>" method="POST" name="ORDER_FORM" id="bx-soa-order-form" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<?
if (strlen($arResult["PREPAY_ADIT_FIELDS"]) > 0)
	echo $arResult["PREPAY_ADIT_FIELDS"];
?>
<input type="hidden" name="action" value="saveOrderAjax">
<input type="hidden" name="location_type" value="code">
<input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="0">
<div id="bx-soa-order" class="row bx-<?=$arParams['TEMPLATE_THEME']?>" style="opacity: 0">
<!--	MAIN BLOCK	-->
	<div class="col-sm-9 bx-soa">
		<div id="bx-soa-main-errors">
			<div class="alert alert-danger" style="display:none"></div>
		</div>
		<!--	AUTH BLOCK	-->
		<div id="bx-soa-auth" class="bx-soa-section bx-soa-auth" style="display:none">
			<div class="bx-soa-section-title-container">
				<h2 class="bx-soa-section-title col-sm-9">
					<span class="bx-soa-section-title-count"></span>
					<?=$arParams['MESS_AUTH_BLOCK_NAME']?>
				</h2>
			</div>
			<div class="bx-soa-section-content container-fluid"></div>
		</div>

		<!--	DUPLICATE MOBILE ORDER SAVE BLOCK	-->
		<div id="bx-soa-total-mobile" style="margin-bottom: 6px;"></div>

		<? if (!isset($arParams['BASKET_POSITION']) || $arParams['BASKET_POSITION'] == 'before'): ?>
			<!--	BASKET ITEMS BLOCK	-->
			<div id="bx-soa-basket" data-visited="false" class="bx-soa-section bx-active">
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
						<?=$arParams['MESS_BASKET_BLOCK_NAME']?>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="javascript:void(0)" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
		<? endif ?>

		<!--	REGION BLOCK	-->
		<div id="bx-soa-region" data-visited="false" class="bx-soa-section" style="display:none;">
			<div class="bx-soa-section-title-container">
				<h2 class="bx-soa-section-title col-sm-9">
					<span class="bx-soa-section-title-count"></span>
					<?=$arParams['MESS_REGION_BLOCK_NAME']?>
				</h2>
				<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
			</div>
			<div class="bx-soa-section-content container-fluid"></div>
		</div>

		<? if ($arParams["DELIVERY_TO_PAYSYSTEM"] == "p2d"): ?>
			<!--	PAY SYSTEMS BLOCK	-->
			<div id="bx-soa-paysystem" data-visited="false" class="bx-soa-section">
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
						<?=$arParams['MESS_PAYMENT_BLOCK_NAME']?>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
			<!--	DELIVERY BLOCK	-->
			<div id="bx-soa-delivery" data-visited="false" class="bx-soa-section" <?=($hideDelivery?'style="display:none"':'')?>>
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
						<?=$arParams['MESS_DELIVERY_BLOCK_NAME']?>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
			<!--	PICKUP BLOCK	-->
			<div id="bx-soa-pickup" data-visited="false" class="bx-soa-section" style="display:none">
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
		<? else: ?>
			<!--	DELIVERY BLOCK	-->
			<div id="bx-soa-delivery" data-visited="false" class="bx-soa-section" <?=($hideDelivery?'style="display:none"':'')?>>
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
						<?=$arParams['MESS_DELIVERY_BLOCK_NAME']?>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
			<!--	PICKUP BLOCK	-->
			<div id="bx-soa-pickup" data-visited="false" class="bx-soa-section" style="display:none">
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
			<!--	PAY SYSTEMS BLOCK	-->
			<div id="bx-soa-paysystem" data-visited="false" class="bx-soa-section">
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
						<?=$arParams['MESS_PAYMENT_BLOCK_NAME']?>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
		<? endif ?>
		<!--	BUYER PROPS BLOCK	-->
		<div id="bx-soa-properties" data-visited="false" class="bx-soa-section bx-active">
			<div class="bx-soa-section-title-container">
				<h2 class="bx-soa-section-title col-sm-9">
					<span class="bx-soa-section-title-count"></span>
					<?=$arParams['MESS_BUYER_BLOCK_NAME']?>
				</h2>
				<div class="col-xs-12 col-sm-3 text-right"><a href="" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
			</div>
			<div class="bx-soa-section-content container-fluid"></div>
		</div>

		<? if ($arParams['BASKET_POSITION'] == 'after'): ?>
			<!--	BASKET ITEMS BLOCK	-->
			<div id="bx-soa-basket" data-visited="false" class="bx-soa-section bx-active">
				<div class="bx-soa-section-title-container">
					<h2 class="bx-soa-section-title col-sm-9">
						<span class="bx-soa-section-title-count"></span>
						<?=$arParams['MESS_BASKET_BLOCK_NAME']?>
					</h2>
					<div class="col-xs-12 col-sm-3 text-right"><a href="javascript:void(0)" class="bx-soa-editstep"><?=$arParams['MESS_EDIT']?></a></div>
				</div>
				<div class="bx-soa-section-content container-fluid"></div>
			</div>
		<? endif ?>

		<!--	ORDER SAVE BLOCK	-->
		<div id="bx-soa-orderSave" class="hidden-xs">
			<a href="javascript:void(0)" style="margin: 10px 0" class="pull-right btn btn-default btn-lg">
				<?=$arParams['MESS_ORDER']?>
			</a>
		</div>

		<div style="display: none;">
			<div id='bx-soa-basket-hidden' class="bx-soa-section"></div>
            <!-- 
			<div id='bx-soa-region-hidden' class="bx-soa-section"></div>
			-->
			<div id='bx-soa-paysystem-hidden' class="bx-soa-section"></div>
			<div id='bx-soa-delivery-hidden' class="bx-soa-section"></div>
			<div id='bx-soa-pickup-hidden' class="bx-soa-section"></div>
			<div id="bx-soa-properties-hidden" class="bx-soa-section"></div>
			<div id="bx-soa-auth-hidden" class="bx-soa-section">
				<div class="bx-soa-section-content container-fluid reg"></div>
			</div>
		</div>
	</div>

	<!--	SIDEBAR BLOCK	-->
	<div id="bx-soa-total" class="col-sm-3 bx-soa-sidebar">
		<div class="bx-soa-cart-total-ghost"></div>
		<div class="bx-soa-cart-total"></div>
	</div>
</div>
</form>

<div id="bx-soa-saved-files" style="display:none"></div>
<div id="bx-soa-soc-auth-services" style="display:none">
	<?
	$arServices = false;
	$arResult["ALLOW_SOCSERV_AUTHORIZATION"] = \Bitrix\Main\Config\Option::get("main", "allow_socserv_authorization", "Y") != "N" ? "Y" : "N";
	$arResult["FOR_INTRANET"] = false;

	if (\Bitrix\Main\ModuleManager::isModuleInstalled("intranet") || \Bitrix\Main\ModuleManager::isModuleInstalled("rest"))
		$arResult["FOR_INTRANET"] = true;

	if (\Bitrix\Main\Loader::includeModule("socialservices") && $arResult["ALLOW_SOCSERV_AUTHORIZATION"] == 'Y')
	{
		$oAuthManager = new CSocServAuthManager();
		$arServices = $oAuthManager->GetActiveAuthServices(array(
			'BACKURL' => $this->arParams['~CURRENT_PAGE'],
			'FOR_INTRANET' => $arResult['FOR_INTRANET'],
		));

		if (!empty($arServices))
		{
			$APPLICATION->IncludeComponent("bitrix:socserv.auth.form", "flat",
				array(
					"AUTH_SERVICES" => $arServices,
					"AUTH_URL" => $arParams["~CURRENT_PAGE"],
					"POST" => $arResult["POST"],
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);
		}
	}
	?>
</div>

<?
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.order.ajax');
$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);
?>

<script type="text/javascript">
	BX.message(<?=CUtil::PhpToJSObject($messages)?>);
	BX.Sale.OrderAjaxComponent.init({
		result: <?=CUtil::PhpToJSObject($arResult['JS_DATA'])?>,
		locations: <?=CUtil::PhpToJSObject($arResult['LOCATIONS'])?>,
		params: <?=CUtil::PhpToJSObject($arParams)?>,
		signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
		siteID: '<?=CUtil::JSEscape(SITE_ID)?>',
		ajaxUrl: '<?=CUtil::JSEscape($this->__component->GetPath().'/ajax.php')?>',
		templateFolder: '<?=CUtil::JSEscape($templateFolder)?>',
		propertyValidation: true,
		showWarnings: true,
		pickUpMap: {
			defaultMapPosition: {
				lat: 55.76,
				lon: 37.64,
				zoom: 7
			},
			secureGeoLocation: false,
			geoLocationMaxTime: 5000,
			minToShowNearestBlock: 3,
			nearestPickUpsToShow: 3
		},
		propertyMap: {
			defaultMapPosition: {
				lat: 55.76,
				lon: 37.64,
				zoom: 7
			}
		},
		orderBlockId: 'bx-soa-order',
		authBlockId: 'bx-soa-auth',
		basketBlockId: 'bx-soa-basket',
		regionBlockId: 'bx-soa-region',
		paySystemBlockId: 'bx-soa-paysystem',
		deliveryBlockId: 'bx-soa-delivery',
		pickUpBlockId: 'bx-soa-pickup',
		propsBlockId: 'bx-soa-properties',
		totalBlockId: 'bx-soa-total'
	});
</script>

<script type="text/javascript">
	<?
	// spike: for children of cities we place this prompt
	$city = \Bitrix\Sale\Location\TypeTable::getList(array('filter' => array('=CODE' => 'CITY'), 'select' => array('ID')))->fetch();
	?>

	BX.saleOrderAjax.init(<?=CUtil::PhpToJSObject(array(
		'source' => $this->__component->getPath().'/get.php',
		'cityTypeId' => intval($city['ID']),
		'messages' => array(
			'otherLocation' => '--- '.Loc::getMessage('SOA_OTHER_LOCATION'),
			'moreInfoLocation' => '--- '.Loc::getMessage('SOA_NOT_SELECTED_ALT'), // spike: for children of cities we place this prompt
			'notFoundPrompt' => '<div class="-bx-popup-special-prompt">'.Loc::getMessage('SOA_LOCATION_NOT_FOUND').'.<br />'.Loc::getMessage('SOA_LOCATION_NOT_FOUND_PROMPT', array(
					'#ANCHOR#' => '<a href="javascript:void(0)" class="-bx-popup-set-mode-add-loc">',
					'#ANCHOR_END#' => '</a>'
				)).'</div>'
		)
	))?>);
</script>
<script>
	(function bx_ymaps_waiter(){
		if (typeof ymaps !== 'undefined')
			ymaps.ready(BX.proxy(BX.Sale.OrderAjaxComponent.initMaps, BX.Sale.OrderAjaxComponent));
		else
			setTimeout(bx_ymaps_waiter, 100);
	})();
	<? if ($arParams['USE_YM_GOALS'] == 'Y'): ?>
	(function bx_counter_waiter(i){
		i = i || 0;
		if (i > 50)
			return;

		if (typeof window['yaCounter<?=$arParams['YM_GOALS_COUNTER']?>'] !== 'undefined')
			BX.Sale.OrderAjaxComponent.reachGoal('initialization');
		else
			setTimeout(function(){bx_counter_waiter(++i)}, 100);
	})();
	<? endif ?>
</script>
<? endif ?>