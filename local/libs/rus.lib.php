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
