<?php
    
    function get_points($points){
        
        $points = preg_replace("#\s#","",$points);
        
        if($points%10==2 && $points!=12){
            return 'балла';
        }
        elseif($points%10==1 && $points!=11){
            return 'балл';
        }
        elseif($points%10==3 && $points!=13){
            return 'балла';
        }
        elseif($points%10==4 && $points!=14){
            return 'балла';
        }
        else{
            return 'баллов';
        }
    }
