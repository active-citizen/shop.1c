<?
    // Загружаем XML
    $xmlImport = file_get_contents($uploadDir.$usersFilename);
    $obImport = simplexml_load_string($xmlImport, "SimpleXMLElement" );

    // Преобразуем объект к массиву
    $arImport = json_decode(json_encode((array)$obImport), TRUE);
    $arUsersCats = [];
    if(
        isset($arImport["КлиентыКатегории"]["КлиентКатегория"])
        &&
        is_array($arImport["КлиентыКатегории"]["КлиентКатегория"])
    )$arUsersCats = $arImport["КлиентыКатегории"]["КлиентКатегория"];
    if(!isset($arUsersCats[0]))$arUsersCats = [$arUsersCats];

    // Создаём индекс пользователей
    $arUsersIndex = [];
    $objUser = new CUser;
    foreach($arUsersCats as $arUsercat)
        if(!isset($arUsersIndex[$arUsercat["Телефон"]]))
            if($arUser = CUser::GetByLogin("u".$arUsercat["Телефон"])->Fetch()){
                $arUsersIndex[$arUsercat["Телефон"]] = $arUser["ID"];
            }
            else{
                $password = mb_substr(md5(rand()),0,10);
                $sEmailRegister = "u".$arUsercat["Телефон"]."@shop.ag.mos.ru";
                $arUserData = [
                    "LOGIN"             =>  "u".$arUsercat["Телефон"],
                    "PASSWORD"          =>  $password,
                    "CONFIRM_PASSWORD"  =>  $password,
                    "EMAIL"             =>  $sEmailRegister,
                    "GROUP_ID"          =>  array(2,3,4,6),
                    "NAME"              =>  $arUsercat["Телефон"], 
                    "LAST_NAME"         =>  "",
                    "PERSONAL_PHONE"    =>  $arUsercat["Телефон"],
                    "ACTIVE"            =>  "Y"
               ];
               if($nUserId = $objUser->Add($arUserData))
                   $arUsersIndex[$arUsercat["Телефон"]] = $nUserId;

            }

    // Определяем список XML_ID категорий для которых определены пользователи
    $arExistsUsercats = [];
    foreach($arUsersCats as $arUserCat)
        if(
            !in_array($arUserCat["Ид"], $arExistsUsercats)
            &&
            isset($arUsersIndex[$arUsercat["Телефон"]])
        )$arExistsUsercats[] = $arUserCat["Ид"];


    // Обнуляем привязки кользователей к существующим в XML категориям
    // Попутно создаём индекс
    $arUsercatsIndex = [];
    foreach($arExistsUsercats as $sXMLID){
        if(!$sXMLID)continue;
        $arUsercat = CIBlockElement::GetList([],$arFilter = [
            "XML_ID"=>$sXMLID, "IBLOCK_ID"=>USERSCATS_IB_ID
        ],false,["nTopCount"=>1],["ID"])->Fetch();
        $arUsercatsIndex[$sXMLID] = $arUsercat["ID"];

        CIBlockElement::SetPropertyValueCode($arUsercat["ID"],"USERS",[]);
    }

    // Создаём массив привязок 
    $arUsercatResult = [];
    foreach($arUsersCats as $arUserCat){
        if(!isset($arUsercatResult[$arUsercatsIndex[$arUserCat["Ид"]]]))
            $arUsercatResult[$arUsercatsIndex[$arUserCat["Ид"]]] = [];

        if(
            isset($arUsersIndex[$arUserCat["Телефон"]])
            &&
            !in_array(
                $arUsersIndex[$arUserCat["Телефон"]],
                $arUsercatResult[$arUserCat["Ид"]] 
            )
        )$arUsercatResult[$arUsercatsIndex[$arUserCat["Ид"]]][] =
         $arUsersIndex[$arUserCat["Телефон"]];
    }


    // Делаем привязки
    foreach($arUsercatResult as $nUsercatId=>$arUsers)
        CIBlockElement::SetPropertyValueCode($nUsercatId,"USERS",$arUsers);

