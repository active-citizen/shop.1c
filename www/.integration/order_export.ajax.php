<?php
    /**
        ������ � ���� XML ��������� ������������� �������
    */
    require("includes/datafilter.lib.php");
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    header("Content-type: text/plain; charset=windows-1251;");
    // �������� DI ������ ������
    $session_id = 
        isset($_COOKIE['PHPSESSID'])
        ?
        $_COOKIE['PHPSESSID']
        :
        "";
    $session_id = 
        !$session_id && isset($_POST['PHPSESSID'])
        ?
        $_POST['PHPSESSID']
        :
        $session_id;
    $session_id = 
        !$session_id && isset($_GET['PHPSESSID'])
        ?
        $_GET['PHPSESSID']
        :
        $session_id;
    if(!preg_match("/^[\d\w]+$/",$session_id)){
        echo "Failed\nPHPSESSID incorrect";
        die;
    }

    echo '<?xml version="1.0" encoding="windows-1251"?>';
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('iblock');
   
    $res = CSaleOrder::GetList(
        array("ID"=>"ASC"),
        array(
            //">ID"=>783
            "DATE_UPDATE"=>""
        ), // �������� ������ �� �������� ������
        false
        ,array("nTopCount"=>ORDER_EXPORT_QUANT)
    );
   
    $objOrder = new CSaleOrder;
    $arOrders = array();
    while($arrOrder = $res->GetNext()){
        // �� ������� ������ ��������������� �� ������ ������
        //if(!$arrOrder["EMP_PAYED_ID"])continue;
        if(!preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"]))continue;

        // �������� ����� ��� "�������� � ������ ������ ������ 
        $objOrder->Update(
            $arrOrder["ID"],
            array(
                "COMMENTS"=>$session_id,
                "DATE_UPDATE"=>"00.00.00 00:00:00"
            )
        );
        
        
        $order = array("��"=>$arrOrder["ID"]);
        $order["�����"] = mb_convert_encoding(
            $arrOrder["ADDITIONAL_INFO"],"cp1251","UTF-8"
        );
        
        $order["����"] = date_parse($arrOrder["DATE_INSERT"]);
        $order["����"] = date("Y-m-d",mktime(
            $order["����"]["hour"],$order["����"]["minute"],
            $order["����"]["second"],
            $order["����"]["month"],$order["����"]["day"],
            $order["����"]["year"]
        ));

        $order["�������������������������"] = date_parse(
            $arrOrder["DATE_INSERT"]
        );
        $order["�������������������������"] = date("Y-m-d",24*60*60+mktime(
            $order["�������������������������"]["hour"],
            $order["�������������������������"]["minute"],
            $order["�������������������������"]["second"],
            $order["�������������������������"]["month"],
            $order["�������������������������"]["day"],
            $order["�������������������������"]["year"]
        ));

        
        $order["�������������"] = date_parse($arrOrder["DATE_UPDATE"]);
        $order["�������������"] = date("Y-m-d H:i:s",mktime(
            $order["�������������"]["hour"],
            $order["�������������"]["minute"],
            $order["�������������"]["second"],
            $order["�������������"]["month"],
            $order["�������������"]["day"],
            $order["�������������"]["year"]
        ));
        
        $order["�����"] = date_parse($arrOrder["DATE_INSERT"]);
        $order["�����"] = date("H:i:s",mktime(
            $order["�����"]["hour"],$order["�����"]["minute"],
            $order["�����"]["second"],
            $order["�����"]["month"],$order["�����"]["day"],
            $order["�����"]["year"]
        ));

        $resProducts = CSaleBasket::GetList(
            array(),array("ORDER_ID"=>$arrOrder["ID"])
        );
        $products = array();
        while($arProduct = $resProducts->GetNext()){
            $product = array();
            $product["����������"] = $arProduct["QUANTITY"];
            
            $resOffer = CIBlockElement::GetList(array(), 
                array(
                    "IBLOCK_ID"=>3,
                    "ID"=>$arProduct["PRODUCT_ID"]
                ),false,array("nTopCount"=>1),
                array("PROPERTY_CML2_LINK","XML_ID","NAME","ID")
            );
            $arOffer = $resOffer->GetNext();
            
            $resProps = CIBlockElement::GetProperty(3, $arOffer["ID"]);
            $product["��������������������"] = array();
            while($arrProp = $resProps->GetNext()){
                if(
                    !preg_match("#^PROP1C_.*#",$arrProp["CODE"]) 
                    || 
                    !$arrProp["VALUE"]
                )continue;
                $product["��������������������"][] = array(
                    "������������"  =>  
                        mb_convert_encoding(
                            dataNormalize($arrProp["NAME"]),"cp1251","utf-8"
                        ),
                    "��������"      =>  
                        mb_convert_encoding(
                            dataNormalize($arrProp["VALUE_ENUM"]),"cp1251","utf-8"
                        )
                );
            }
            
            $resPrice = CPrice::GetList(array(),
                array("PRODUCT_ID"=>$arOffer["ID"]),false,array("nTopCount"=>1),
                array("PRICE")
            );
            $arPrice = $resPrice->GetNext();
            
            $resCatalog = CIBlockElement::GetList(array(), 
                array(
                    "IBLOCK_ID"=>CATALOG_IB_ID,
                    "ID"=>$arrOffer["PROPERTY_CML2_LINK_VALUE"]),
                    false,
                    array("nTopCount"=>1),
                    array("PROPERTY_QUANT","PROPERTY_ARTNUMBER")
            );
            $arrCatalog = $resCatalog->GetNext();
            
            $product["��"] = $arOffer["XML_ID"];
            $product["������������"] = 
                mb_convert_encoding(dataNormalize($arOffer["NAME"]),"cp1251","utf-8");
            $product["�������"] = 
                mb_convert_encoding(
                    dataNormalize($arrCatalog["PROPERTY_QUANT_VALUE"]),"cp1251","utf-8"
                );
            $product["�������"] = $arrCatalog["PROPERTY_ARTNUMBER_VALUE"];
            $product["�������������"] = $arPrice["PRICE"];
            $product["�������"] = $arOffer;
            $products[] = $product;
        }

        //$order["�����"] = $arrOrder["SUM_PAID"];
        $order["�����"] = 0;
        foreach($products as $product)
            $order["�����"] += $product["����������"]*$product["�������������"];
        
        $resUser = CUser::GetByID($arrOrder["USER_ID"]);
        $arUser = $resUser->GetNext();
        
        $resStore = CCatalogStore::GetList(
            array(),
            array("ID"=>$arrOrder["STORE_ID"]),
            false,
            array("nTopCount"=>1)
        );
        $arStore = $resStore->GetNext();
        
        
        $order["�������"] = preg_replace("#^u(\d+)$#","$1",$arUser["LOGIN"]);
        $order["����������������"] = $arUser["EMAIL"];
        $order["������"] = 
            mb_convert_encoding(
                dataNormalize($arUser["LAST_NAME"]." ".$arUser["NAME"]),"cp1251","utf-8"
            );
        $order["���"] = mb_convert_encoding(
            dataNormalize($arUser["NAME"]),"cp1251","utf-8"
        );
        $order["�������"] = mb_convert_encoding(
            dataNormalize($arUser["LAST_NAME"]),"cp1251","utf-8"
        );
        $order["�����"] = mb_convert_encoding(
            dataNormalize($arUser["PERSONAL_CITY"]),"cp1251","utf-8"
        );
        $order["�����"] = $arStore["XML_ID"];
        
        $arSatatus = CSaleStatus::GetByID($arrOrder["STATUS_ID"]);
        $order["������������������"] = $arrOrder["STATUS_ID"];
        $order["���������������"] = mb_convert_encoding(
            $arSatatus["NAME"],"cp1251","utf-8"
        );
        $order["������"] = $products;
        $arOrders[] = $order;
    }
?><���������������������� xmlns="urn:1C.ru:commerceml_205" <? 
?>xmlns:xs="http://www.w3.org/2001/XMLSchema" <?
?>xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" <?
?>�����������="2.05" ����������������="<? echo date("c");?>">
<? foreach($arOrders as $arOrder):?>
<��������>
    <��><? echo $arOrder["��"];?></��>
    <�����><? echo $arOrder["�����"];?></�����>
    <����><? echo $arOrder["����"];?></����>
    <�����><? echo $arOrder["�����"];?></�����>
    <������>���.</������>
    <����>1</����>
    <�����������>����� ������</�����������>
    <����>��������</����>
    <�����><? echo $arOrder["�����"];?></�����>
    <�����������/>
    <�������������������������><? 
        echo $arOrder["�������������������������"];
    ?></�������������������������>
    <�������������><? echo $arOrder["�������������"];?></�������������>
    <������������><? echo $arOrder["���������������"];?></������������>
    <�����������������><? 
        echo $arOrder["���������������"];
    ?></�����������������>
    <�����������>
        <����������>
            <��>0#<? echo $arOrder["����������������"];?></��>
            <������������><? echo $arOrder["������"];?></������������>
            <����>����������</����>
            <������������������><? 
                echo $arOrder["������"];
            ?></������������������>
            <�������><? echo $arOrder["�������"];?></�������>
            <���><? echo $arOrder["���"];?></���>
            <�����>
                <�������������><? echo $arOrder["�����"];?></�������������>
            </�����>
            <��������>
                <�������>
                    <���>��������������</���>
                    <��������><? echo $arOrder["�������"];?></��������>
                </�������>
                <�������>
                    <���>�����</���>
                    <��������><? echo $arOrder["����������������"];?></��������>
                </�������>
            </��������>
        </����������>
    </�����������>
    <�������>
        <���������>
            <�������������><? echo $arOrder["�������������"];?></�������������>
            <���������������><? 
                echo $arOrder["���������������"];
            ?></���������������>
            <�����������������><? 
                echo $arOrder["���������������"];
            ?></�����������������>
            <�����������/>
            <�����������>���</�����������>
        </���������>
    </�������>
    <������>    
        <? foreach($arOrder["������"] as $product):?>
        <�����>
            <��><? echo $product["��"];?></��>
            <������������><? echo $product["������������"];?></������������>
            <�������������><? echo $product["�������������"];?></�������������>
            <�������������������><? 
                echo $product["�������������"];
            ?></�������������������>
            <����������><? echo $product["����������"];?></����������>
            <�����><? 
                echo 
                    number_format($product["����������"]
                    *$product["�������������"],2,'.',' ');
            ?></�����>
            <�����><? echo $arOrder["�����"];?></�����>
            <��������������������>
                <? foreach($product["��������������������"] as $arProps):?>
                <��������������������>
                    <������������><? 
                        echo $arProps["������������"]
                    ?></������������>
                    <��������><? echo $arProps["��������"]?></��������>
                </��������������������>
                <? endforeach?>
            </��������������������>
        </�����>
        <? endforeach?>
    </������>
</��������>
<? endforeach?>
</����������������������>
<?require(
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
);?>


