<?php

    require("include/common.php");

    $arSessions = [];
    for($i = 0;$i<100;$i++){;
        $nN = sprintf("%02d",$i);
        $GLOBALS["DB"]->search(["a"=>"sessions_".$nN],[],["user_id"=>0]);
        $arSessions = array_merge($arSessions, $GLOBALS["DB"]->rows);
        foreach($GLOBALS["DB"]->rows as $arRow){
            $GLOBALS["DB"]->delete("sessions_".$nN,["user_id"=>0],"",0);
        }
        echo "<pre>$nN";
        print_r($GLOBALS["DB"]->rows);
        echo "</pre>";
    }
    $GLOBALS["DB"]->delete("transacts_detail_00",[],
        "transaction_id IN (SELECT `a`.`id` FROM `transacts_brief_00` as `a`
        WHERE `a`.`user_id`=0)"
    ,0);
    $GLOBALS["DB"]->delete("transacts_brief_00",["user_id"=>0], "" ,0);

    $GLOBALS["DB"]->sql_query("OPTIMIZE TABLE `transacts_brief_00`","CHANGE");
    $GLOBALS["DB"]->sql_query("OPTIMIZE TABLE `transacts_detail_00`","CHANGE");
?>
<script>
setTimeout(function(){document.location.href='/api/sess.php';},30000);
</script>



