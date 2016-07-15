<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	use Bitrix\Main\Localization\Loc;

	Loc::loadMessages(__FILE__);

	// we dont trust input params, so validation is required
	$legalColors = array(
		'green' => true,
		'yellow' => true,
		'red' => true,
		'gray' => true
	);
	// default colors in case parameters unset
	$defaultColors = array(
		'N' => 'green',
		'P' => 'yellow',
		'F' => 'gray',
		'PSEUDO_CANCELLED' => 'red'
	);

	foreach ($arParams as $key => $val)
		if(strpos($key, "STATUS_COLOR_") !== false && !$legalColors[$val])
			unset($arParams[$key]);

	// to make orders follow in right status order
	if(is_array($arResult['INFO']) && !empty($arResult['INFO']))
	{
		foreach($arResult['INFO']['STATUS'] as $id => $stat)
		{
			$arResult['INFO']['STATUS'][$id]["COLOR"] = $arParams['STATUS_COLOR_'.$id] ? $arParams['STATUS_COLOR_'.$id] : (isset($defaultColors[$id]) ? $defaultColors[$id] : 'gray');
			$arResult["ORDER_BY_STATUS"][$id] = array();
		}
	}
	$arResult["ORDER_BY_STATUS"]["PSEUDO_CANCELLED"] = array();

	$arResult["INFO"]["STATUS"]["PSEUDO_CANCELLED"] = array(
		"NAME" => Loc::getMessage('SPOL_PSEUDO_CANCELLED'),
		"COLOR" => $arParams['STATUS_COLOR_PSEUDO_CANCELLED'] ? $arParams['STATUS_COLOR_PSEUDO_CANCELLED'] : (isset($defaultColors['PSEUDO_CANCELLED']) ? $defaultColors['PSEUDO_CANCELLED'] : 'gray')
	);

	if(is_array($arResult["ORDERS"]) && !empty($arResult["ORDERS"]))
	{
		foreach ($arResult["ORDERS"] as $order)
		{
			$order['HAS_DELIVERY'] = intval($order["ORDER"]["DELIVERY_ID"]) || strpos($order["ORDER"]["DELIVERY_ID"], ":") !== false;

			$stat = $order['ORDER']['CANCELED'] == 'Y' ? 'PSEUDO_CANCELLED' : $order["ORDER"]["STATUS_ID"];
			$color = $arParams['STATUS_COLOR_'.$stat];
			$order['STATUS_COLOR_CLASS'] = empty($color) ? 'gray' : $color;

			$arResult["ORDER_BY_STATUS"][$stat][] = $order;
		}
	}
    
    // Определяем для каждого заказа возможность его отменить
    $res = CIBlockElement::GetList(array(),array("IBLOCK_CODE"=>"clothes_offers"),false,array("nTopCount"=>1),array("IBLOCK_ID"));
    $res = $res->GetNext();
    $IBlockId = $res["IBLOCK_ID"];
    
    $res = CIBlockElement::GetList(array(),array("IBLOCK_CODE"=>"clothes"),false,array("nTopCount"=>1),array("IBLOCK_ID"));
    $res = $res->GetNext();
    $IBlockId2 = $res["IBLOCK_ID"];


    foreach($arResult["ORDER_BY_STATUS"]["N"] as $orderKey => $order){
        $canCancel = true;
        foreach($order["BASKET_ITEMS"] as $basketItem=>$product){
            $res = CIBlockElement::GetProperty($IBlockId,$product["PRODUCT_ID"],array(), array("CODE"=>"CML2_LINK"));
            
            while($row = $res->GetNext()){
                if(!$row["VALUE"])continue;
                $res2 = CIBlockElement::GetProperty($IBlockId2,$row["VALUE"],array(), array("CODE"=>"CANCEL_ABILITY"));
                $prop = $res2->GetNext();
                $arResult["ORDER_BY_STATUS"]["N"][$orderKey]["CANCEL_ABILITY"] = $prop["VALUE_ENUM"];
                if(!$prop["VALUE_ENUM"])$canCancel = false;
            }
            
            if(!$canCancel)break;
        }
    }
    
    
    
?>
