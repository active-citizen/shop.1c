<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Iblock;

if (!Loader::includeModule('sale'))
	return;

$arColumns = array(
	"NAME" => GetMessage("SBB_BNAME"),
	"DISCOUNT" => GetMessage("SBB_BDISCOUNT"),
	"WEIGHT" => GetMessage("SBB_BWEIGHT"),
	"PROPS" => GetMessage("SBB_BPROPS"),
	"DELETE" => GetMessage("SBB_BDELETE"),
	"DELAY" => GetMessage("SBB_BDELAY"),
	"TYPE" => GetMessage("SBB_BTYPE"),
	"PRICE" => GetMessage("SBB_BPRICE"),
	"QUANTITY" => GetMessage("SBB_BQUANTITY"),
	"SUM" => GetMessage("SBB_BSUM")
);

if (Loader::includeModule('catalog'))
{
	$arIblockIDs = array();
	$arIblockNames = array();
	$catalogIterator = Catalog\CatalogIblockTable::getList(array(
		'select' => array('IBLOCK_ID', 'NAME' => 'IBLOCK.NAME'),
		'order' => array('IBLOCK_ID' => 'ASC')
	));
	while ($catalog = $catalogIterator->fetch())
	{
		$catalog['IBLOCK_ID'] = (int)$catalog['IBLOCK_ID'];
		$arIblockIDs[] = $catalog['IBLOCK_ID'];
		$arIblockNames[$catalog['IBLOCK_ID']] = $catalog['NAME'];
	}
	unset($catalog, $catalogIterator);

	if (!empty($arIblockIDs))
	{
		$arProps = array();
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'CODE', 'NAME', 'IBLOCK_ID'),
			'filter' => array('@IBLOCK_ID' => $arIblockIDs, '=ACTIVE' => 'Y', '!=XML_ID' => CIBlockPropertyTools::XML_SKU_LINK),
			'order' => array('IBLOCK_ID' => 'ASC', 'SORT' => 'ASC', 'ID' => 'ASC')
		));
		while ($property = $propertyIterator->fetch())
		{
			$property['ID'] = (int)$property['ID'];
			$property['IBLOCK_ID'] = (int)$property['IBLOCK_ID'];
			$property['CODE'] = (string)$property['CODE'];
			if ($property['CODE'] == '')
				$property['CODE'] = $property['ID'];
			if (!isset($arProps[$property['CODE']]))
			{
				$arProps[$property['CODE']] = array(
					'CODE' => $property['CODE'],
					'TITLE' => $property['NAME'].' ['.$property['CODE'].']',
					'ID' => array($property['ID']),
					'IBLOCK_ID' => array($property['IBLOCK_ID'] => $property['IBLOCK_ID']),
					'IBLOCK_TITLE' => array($property['IBLOCK_ID'] => $arIblockNames[$property['IBLOCK_ID']]),
					'COUNT' => 1
				);
			}
			else
			{
				$arProps[$property['CODE']]['ID'][] = $property['ID'];
				$arProps[$property['CODE']]['IBLOCK_ID'][$property['IBLOCK_ID']] = $property['IBLOCK_ID'];
				if ($arProps[$property['CODE']]['COUNT'] < 2)
					$arProps[$property['CODE']]['IBLOCK_TITLE'][$property['IBLOCK_ID']] = $arIblockNames[$property['IBLOCK_ID']];
				$arProps[$property['CODE']]['COUNT']++;
			}
		}
		unset($property, $propertyIterator, $arIblockNames, $arIblockIDs);

		$propList = array();
		foreach ($arProps as &$property)
		{
			$iblockList = '';
			if ($property['COUNT'] > 1)
			{
				$iblockList = ($property['COUNT'] > 2 ? ' ( ... )' : ' ('.implode(', ', $property['IBLOCK_TITLE']).')');
			}
			$propList['PROPERTY_'.$property['CODE']] = $property['TITLE'].$iblockList;
		}
		unset($property, $arProps);

		if (!empty($propList))
			$arColumns = array_merge($arColumns, $propList);
		unset($propList);
	}
}

$arYesNo = Array(
	"Y" => GetMessage("SBB_DESC_YES"),
	"N" => GetMessage("SBB_DESC_NO"),
);

$arComponentParameters = Array(
	"GROUPS" => array(
		"OFFERS_PROPS" => array(
			"NAME" => GetMessage("SBB_OFFERS_PROPS"),
		),
		"GIFTS" => array(
			"NAME" => GetMessage("SBB_GIFTS"),
		),
	),
	"PARAMETERS" => Array(
		"PATH_TO_ORDER" => Array(
			"NAME" => GetMessage("SBB_PATH_TO_ORDER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"HIDE_COUPON" => Array(
			"NAME"=>GetMessage("SBB_HIDE_COUPON"),
			"TYPE"=>"LIST", "MULTIPLE"=>"N",
			"VALUES"=>array(
					"N" => GetMessage("SBB_DESC_NO"),
					"Y" => GetMessage("SBB_DESC_YES")
				),
			"DEFAULT"=>"N",
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"COLUMNS_LIST" => Array(
			"NAME"=>GetMessage("SBB_COLUMNS_LIST"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"Y",
			"VALUES"=>$arColumns,
			"DEFAULT"=>array("NAME", "PRICE", "TYPE", "DISCOUNT", "QUANTITY", "DELETE", "DELAY", "WEIGHT"),
			"COLS"=>25,
			"SIZE"=>7,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "VISUAL",
		),

/*
		"PRICE_VAT_INCLUDE" => array(
			"NAME" => GetMessage('SBB_VAT_INCLUDE'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
*/
		"PRICE_VAT_SHOW_VALUE" => array(
			"NAME" => GetMessage('SBB_VAT_SHOW_VALUE'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => Array(
			"NAME"=>GetMessage("SBB_COUNT_DISCOUNT_4_ALL_QUANTITY"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT"=>"N",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"USE_PREPAYMENT" => array(
			"NAME" => GetMessage('SBB_USE_PREPAYMENT'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"QUANTITY_FLOAT" => array(
			"NAME" => GetMessage('SBB_QUANTITY_FLOAT'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"AUTO_CALCULATION" => array(
			"NAME" => GetMessage('SBB_AUTO_CALCULATION'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" => Array(),
		"ACTION_VARIABLE" => array(
			"NAME" => GetMessage('SBB_ACTION_VARIABLE'),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "basketAction",
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"USE_GIFTS" => array(
			"PARENT" => "GIFTS",
			"NAME" => GetMessage("SBB_GIFTS_USE_GIFTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		),
	),
);

if(!Loader::includeModule('catalog'))
{
	unset($arComponentParameters["PARAMETERS"]["USE_GIFTS"]);
	unset($arComponentParameters["GROUPS"]["GIFTS"]);
}
elseif($arCurrentValues["USE_GIFTS"] === null && $arComponentParameters['PARAMETERS']['USE_GIFTS']['DEFAULT'] == 'Y' || $arCurrentValues["USE_GIFTS"] == "Y")
{
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			"GIFTS_PLACE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_PLACE_GIFT"),
				"TYPE" => "LIST",
				"DEFAULT" => "BOTTOM",
				"VALUES" => array(
					"TOP" => GetMessage('SGB_PARAMS_PLACE_GIFT_TOP'),
					"BOTTOM" => GetMessage('SGB_PARAMS_PLACE_GIFT_BOTTOM'),
				),
			),
			"GIFTS_BLOCK_TITLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_BLOCK_TITLE"),
				"TYPE" => "STRING",
				"DEFAULT" => GetMessage("SGB_PARAMS_BLOCK_TITLE_DEFAULT"),
			),
			"GIFTS_HIDE_BLOCK_TITLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_HIDE_BLOCK_TITLE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "",
			),
			"GIFTS_TEXT_LABEL_GIFT" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_TEXT_LABEL_GIFT"),
				"TYPE" => "STRING",
				"DEFAULT" => GetMessage("SGB_PARAMS_TEXT_LABEL_GIFT_DEFAULT"),
			),
			"GIFTS_PRODUCT_QUANTITY_VARIABLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_PRODUCT_QUANTITY_VARIABLE"),
				"TYPE" => "STRING",
				"DEFAULT" => "quantity",
				"HIDDEN" => (isset($arCurrentValues['USE_PRODUCT_QUANTITY']) && $arCurrentValues['USE_PRODUCT_QUANTITY'] == 'Y' ? 'N' : 'Y')
			),
			"GIFTS_PRODUCT_PROPS_VARIABLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_PRODUCT_PROPS_VARIABLE"),
				"TYPE" => "STRING",
				"DEFAULT" => "prop",
				"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
			),
			"GIFTS_SHOW_OLD_PRICE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_SHOW_OLD_PRICE"),
				"TYPE" => "CHECKBOX",
				"VALUES" => "Y",
			),
			'GIFTS_SHOW_DISCOUNT_PERCENT' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_SHOW_DISCOUNT_PERCENT'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'Y'
			),
			"GIFTS_SHOW_NAME" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_SHOW_NAME"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
			),
			"GIFTS_SHOW_IMAGE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_SHOW_IMAGE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
			),
			'GIFTS_MESS_BTN_BUY' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_MESS_BTN_BUY_GIFT'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CVP_MESS_BTN_BUY_GIFT_DEFAULT')
			),
			'GIFTS_MESS_BTN_DETAIL' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_MESS_BTN_DETAIL'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CVP_MESS_BTN_DETAIL_DEFAULT')
			),
			"GIFTS_PAGE_ELEMENT_COUNT" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PAGE_ELEMENT_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => "4",
			),
			'GIFTS_CONVERT_CURRENCY' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_CONVERT_CURRENCY'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N',
				'REFRESH' => 'Y',
			),
			'GIFTS_HIDE_NOT_AVAILABLE' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_HIDE_NOT_AVAILABLE'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N',
			),
		)
	);
}