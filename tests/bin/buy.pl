#!/usr/bin/perl

print "

Скрипт заказывает указанное торговое предложения для случайных пользователей из
списка.

Использование:
buy.pl base_domain sessions_file
 - domain
 - product_url - ссылка на товар (/catalog/eksklyuziv/indulgentsiya-10186cb8/)
 - sessions_file - файл с сессиями в формате csv вида
    ID записи  LOGIN    EMAIL   LAST_UPDATE SESSION_ID
 - offer_id - ID торгового предложения (2061)
 - store_id - ID склада (35)
 - count - количество случайных заказов 

    
" unless $ARGV[0] && $ARGV[1] && $ARGV[2] && $ARGV[3] && $ARGV[4] && $ARGV[5];

$sDomain = $ARGV[0];
$sUrl01 = "http://".$ARGV[0].$ARGV[1];
$sUrl02 = "http://".$ARGV[0]
    ."/profile/order/order.ajax.php?add_order=1&id=".$ARGV[3]
    ."&quantity=1&store_id=".$ARGV[4];
$sSessionsFile = $ARGV[2];
$nCount = $ARGV[5];

open(A,$sSessionsFile);
@arSessions = <A>;
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
      
    
    $command02 = "wget -o /dev/null -O /dev/stdout "
        ." --header='Cookie:EMPSESSION=$sSessionId'"
        ." '$sUrl02'";
        ;
    `$command01`;
    `$command02`;
    print "\n$sRow\n";
}

