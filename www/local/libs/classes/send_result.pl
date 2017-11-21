#!/usr/bin/perl 

chdir("/home/bitrix/www/local/libs/classes/");
@output = `/usr/bin/perl unittests.pl`;
chomp(@output);

$sMailText = "";
print $sMailText = $sMailText.$_."\n" foreach @output;

$bResult = 'OK';

foreach $sTest(@output){
    $bResult = 'FAILED' if $sTest=~/failed/i;
}

$sMail = "To: petrovvv\@em.mos.ru
FROM: shop\@ag.mos.ru
Content-Type: text/plain
Subject: [$bResult] agshop. Unittest. 

".$sMailText;

open(A,">/home/bitrix/mail_queue/unittests01.eml");
print A $sMail;
close(A);

$sMail = "To: andrey.inyutsin\@altarix.ru
FROM: shop\@ag.mos.ru
Content-Type: text/plain
Subject: [$bResult] agshop. Unittest. 

".$sMailText;

open(A,">/home/bitrix/mail_queue/unittests02.eml");
print A $sMail;
close(A);


