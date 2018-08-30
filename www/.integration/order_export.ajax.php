<?php
    /**
        ������ � ���� XML ��������� ������������� �������
    */
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/COrder/COrderExportCML.class.php");
    use AGShop\Order as Order;

    header("Content-type: text/plain; charset=windows-1251;");
    if(!$USER->IsAdmin()){
        echo "Failed\nAccess denied";
        die;
    }

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
    

    $objExport = new \Order\COrderExportCML;
    $arOrders = $objExport->getLastZNI($session_id);
    echo '<?xml version="1.0" encoding="windows-1251"?>'."\n";

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
    <����>1</����><? 
        if($arOrder["�����������"]):
    ?>

    <�����������><? echo $arOrder["�����������"];?></�����������><?
        endif
    ?>
    <? 
        if($arOrder["�������������"]):
    ?>

    <�������������><? echo $arOrder["�������������"];
    ?></�������������><?
        endif
    ?>

    <�����������>����� ������</�����������>
    <����>��������</����>
    <�����><? echo $arOrder["�����"];?></�����>
    <�����������/>
    <? if($order["�������������������������"]):?>
    <�������������������������><? 
        echo $arOrder["�������������������������"];
    ?></�������������������������>
    <? endif ?>
    <�������������><? echo $arOrder["�������������"];?></�������������>
    <? if($arOrder["��������������"]):?>
    <��������������><? echo $arOrder["��������������"];?></��������������>
    <? endif ?>
    <������������><? echo $arOrder["���������������"];?></������������>
    <�����������������><? 
        echo $arOrder["���"];
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
                echo $arOrder["���"];
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
	    <�������������������><? echo $product["�������������"];?></�������������������>
            <����������><? echo intval($product["����������"]);?></����������>
            <�����><? 
                echo 
                    number_format($product["����������"]
                    *$product["�������������"],2,'.','');
            ?></�����>
            <�����><? echo $arOrder["�����"];?></�����>
            <��������������������>
                <? foreach($product["��������������������"] as $arProps):?>
                <��������������������>
                    <!--
                    <������������><? 
                        echo $arProps["������������"]
                    ?></������������>
                    <��������><? echo trim($arProps["��������"])?></��������>
                    -->
                    <<? 
                        echo trim($arProps["������������"])
                    ?>><? echo $arProps["��������"]?>
                    </<? 
                        echo trim($arProps["������������"])
                    ?>>
                </��������������������>
                <? endforeach?>
            </��������������������>
        </�����>
        <? endforeach?>
    </������>
</��������>
<? endforeach?>
</����������������������>
<?
    // ������� ����������
    $objExport->orderQueryResetLock();
    require(
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
);?>
