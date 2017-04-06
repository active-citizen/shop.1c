<?
    function dataNormalize($string){
        $pattern = "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ".
        "абвгдеёжзийклмнопрстуфхцчшщъыьяэюя".
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ".
        "abcdefghijklmnopqrstuvwxyz".
        "0123456789".
        "_ .,-";
        for($i=0,$c=mb_strlen($string,"UTF-8");$i<$c;$i++)
            if(mb_strpos($pattern,mb_substr($string,$i,1,"UTF-8"))===false)
                $string =
                mb_substr($string,0,$i,"UTF-8")."_".mb_substr($string,
                $i+1,NULL,"UTF-8");

        return $string;
    }


