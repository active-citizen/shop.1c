<?php
    
    function get_points($points){
        
        $points = preg_replace("#\s#","",$points);
        
        if($points%100==11){
            return 'баллов';
        }
        elseif($points%100==12){
            return 'баллов';
        }
        elseif($points%100==13){
            return 'баллов';
        }
        elseif($points%100==14){
            return 'баллов';
        }
        elseif($points%10==1){
            return 'балл';
        }
        elseif($points%10==3){
            return 'балла';
        }
        elseif($points%10==4){
            return 'балла';
        }
        else{
            return 'баллов';
        }
    }

    function get_days($points){
        
        $points = preg_replace("#\s#","",$points);
        
        if($points%100==11){
            return 'дней';
        }
        elseif($points%100==12){
            return 'дней';
        }
        elseif($points%100==13){
            return 'дней';
        }
        elseif($points%100==14){
            return 'дней';
        }
        elseif($points%10==1){
            return 'день';
        }
        elseif($points%10==3){
            return 'дня';
        }
        elseif($points%10==4){
            return 'дня';
        }
        else{
            return 'дней';
        }
    }

    function get_date($date=''){
        $mons = array(
            1=>"января",
            2=>"февраля",
            3=>"марта",
            4=>"апреля",
            5=>"мая",
            6=>"июня",
            7=>"июля",
            8=>"августа",
            9=>"сентября",
            10=>"октября",
            11=>"ноября",
            12=>"декабря",
        );

        if(!$date)$date = date("d.m.Y");
        $tmp = date_parse($date);
        
        return 
            $tmp["day"]
            ." ".$mons[intval($tmp["month"])]
            ." ".$tmp["year"];

    }

