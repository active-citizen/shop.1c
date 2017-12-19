#!/usr/bin/perl

print "

Скрипт просмотра указанного товара

Использование:
buy.pl base_domain sessions_file
 - domain
 - product_url - ссылка на товар (/catalog/eksklyuziv/indulgentsiya-10186cb8/)
 - sessions_file - файл с сессиями в формате csv вида
    ID записи  LOGIN    EMAIL   LAST_UPDATE SESSION_ID
 - count - количество случайных заказов 

    
" unless $ARGV[0] && $ARGV[1] && $ARGV[2] && $ARGV[3];

$sDomain = $ARGV[0];
$sUrl01 = "http://".$ARGV[0].$ARGV[1];
$sSessionsFile = $ARGV[2];
$nCount = $ARGV[3];

open(A,$sSessionsFile);
@arSessions = <A>;
fork();
fork();
fork();
fork();
fork();
fork();
fork();
fork();
fork();
for($i=0;$i<$nCount;$i++){
    $sRow = $arSessions[
        # 0
        rand(scalar(@arSessions))
    ];
    ($nId,$sLogin,$sEmail,$sLastUpdate,$sSessionId) = split("\t",$sRow);
    
    $command01 = "wget -o /dev/null -O /dev/stdout "
        ." --header='Cookie:EMPSESSION=$sSessionId'"
        ." '$sUrl01'";
      
    
    `$command01`;
    print "\n$sRow\n";
}

