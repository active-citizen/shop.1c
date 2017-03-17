<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$answer = array("error"=>"");

$productId = isset($_POST["productid"])?intval($_POST["productid"]):0;
$mark = isset($_POST["mark"])?intval($_POST["mark"]):0;
$comment = isset($_POST["comment"])?$_POST["comment"]:0;

if(!$comment){
    $answer["error"] = 'Нет текста комментария';
}
elseif(!$mark){
    $answer["error"] = 'Нет оценки';
}
elseif(!$productId){
    $answer["error"] = 'Нет ID продукта';
}
else{
    CModule::IncludeModule('forum');
    CModule::IncludeModule('iblock');
    
    $arForum = CForumNew::GetList()->GetNext();
    $forumId = $arForum["ID"];
    
    //$arForumTopic = CForumTopic::GetList(array(),array("FORUM_ID"=>$forumId,"ID"=>))->GetNext();
    
    $arIBlock = CIBlock::GetList(array(),array("CODE"=>"clothes"))->GetNext();
    $arTopic = CIBlockElement::GetProperty($arIBlock["ID"],$productId,array(),array("CODE"=>"FORUM_TOPIC_ID"))->GetNext();
    $catalogIblockId = $arIBlock["ID"];

    if(!$arTopic["VALUE"]){
        $topicId = CForumTopic::Add(array(
            "TITLE"=>"Product $productId",
            "STATE"=>"Y",
            'USER_START_ID'=>$USER->GetID(),
            'USER_START_NAME'=>$USER->GetLogin(),
            'LAST_POSTER_NAME'=>$USER->GetLogin(),
            'START_DATE'=>date("Y-m-d H:i:s",time()),
            'FORUM_ID'=>intval($forumId),
        ));
        CIBlockElement::SetPropertyValues($productId,$arIBlock["ID"],$topicId,"FORUM_TOPIC_ID");
    }
    else{
        $topicId = $arTopic["VALUE"];
    }
    
    $userInfo = $USER->GetById($USER->GetId())->GetNext();
    
    $AUTHOR_NAME = 
        ($userInfo["NAME"] || $userInfo["LAST_NAME"])
        ?
        $userInfo["LAST_NAME"]." ".$userInfo["NAME"]
        :
        $userInfo["LOGIN"];
    
    $arFields = array(
        "POST_MESSAGE" => $comment,
        "USE_SMILES" => "Y",
        "APPROVED" => "Y",
        "AUTHOR_NAME" => $AUTHOR_NAME,
        "AUTHOR_ID" => $USER->GetId(),
        "FORUM_ID" => $forumId,
        "TOPIC_ID" => $topicId,
        "AUTHOR_IP" => $_SERVER["REMOTE_ADDR"],
        "NEW_TOPIC" => "N"    
    );

    $objForumMessage = new CForumMessage;
    if(!$messageId = $objForumMessage->Add($arFields)){
        $answer["error"] = "Ошибка добавления сообщения ".print_r($arFields,1);
    }
    
    // Добавляем оценку
    if(!$answer["error"]){
        // Проверяем наличие оценки
        $arIBlock = CIBlock::GetList(array(),array("CODE"=>"marks"))->GetNext();
        if(!$arMark = CIBlockElement::GetList(
            array(), 
            array(
                "IBLOCK_ID"=>$arIBlock["ID"],
                "PROPERTY_MARK_USER"=>$USER->GetId(),
                "PROPERTY_MARK_PRODUCT"=>$productId
            )
        )->GetNext()){
            $objMarks = new CIBlockElement;
            if($id = $objMarks->Add(array("IBLOCK_ID"=>$arIBlock["ID"],"NAME"=>"MARK_".$USER->GetId()."_".rand()))){
                CIBlockElement::SetPropertyValues($id,$arIBlock["ID"],$USER->GetId(),"MARK_USER");
                CIBlockElement::SetPropertyValues($id,$arIBlock["ID"],$productId,"MARK_PRODUCT");
                CIBlockElement::SetPropertyValues($id,$arIBlock["ID"],$mark,"MARK");
                // пересчёт рейтинга
                $resMarks = CIBlockElement::GetList(
                    array(), 
                    $arFields = array(
                        "IBLOCK_ID"=>$arIBlock["ID"],
                        "PROPERTY_MARK_PRODUCT"=>$productId
                    ),
                    false,
                    array(),
                    array("PROPERTY_MARK")
                );
                $count = 0;
                $sum = 0;
                while($arMark=$resMarks->GetNext()){
                    $sum+=$arMark['PROPERTY_MARK_VALUE'];
                    $count++;
                }
                if($count)CIBlockElement::SetPropertyValues($productId,$catalogIblockId,$sum/$count,"RATING");
            }
            else{
                echo "<pre>";
                $answer["error"] = $objMarks->LAST_ERROR;
            }
        }
    }
    
    
}

echo json_encode($answer);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
