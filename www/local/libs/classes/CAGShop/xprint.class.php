<?php

/**
    Альтернатива print_r или var_dump
*/
class Xprint{
    function __construct($var, $halt=0){
        self::item($var);
        if($halt)exit();
    }
    
    static function item($var){
        $parents = array();
        $value = '';
        if(is_object($var)){
            $parents = get_object_vars($var);
            echo "object(".get_class($var).")";
        }
        elseif(is_array($var)){
            $parents = $var;
            echo "array(".count($var).")";
        }
        else{echo $var;}

        
        foreach($parents as $key=>$value){
            if(is_array($value) || is_object($value)){
                echo '<div class="xprint">';
                echo '<span class="xprint-key">'.$key.'</span>';
                // Чтобы понять рекурсию, надо сперва понять рекурсию
                self::item($value);
                echo "</div>";
            }
            else{
                echo '<div class="xprint-scalar"><span class="xprint-key">'.$key.':</span>';
                echo '<span class="xprint-value">"'.htmlspecialchars($value).'"</span></div>';
            }
        }
        ?>
        <style>
            .xprint{padding-left: 20px;font-family: monospace;}
            .xprint-scalar{margin-left: 20px;}
            .xprint-key{font-weight: bold;margin-right: 20px;padding-left: 5px;
                border-left: 1px #888 solid;border-bottom: 1px #888 solid;}
            .xprint-value{font-style: italic;margin-left:  20px;}
            .xprint-scalar:hover{background-color: #EEE;font-size: 1.5em;}
        </style>
        <?
    }
}

