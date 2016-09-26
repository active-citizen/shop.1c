#!/usr/bin/perl 

$PHPSESSID  = '93ffmost2tu3nbcn1ipdloubs5';
$SESSION_ID = '36388acaf5084c28ce9a54858be49df0';
$DOMAIN     = "shop.ag.mos.ru.local";

$ZIP_FILENAME = "v8_BD31_50.zip";
$IMPORT_FILENAME = "import9_23_14_12_4.xml";
$OFFERS_FILENAME = "offers9_23_14_12_4.xml";
$FILES_FILENAME = "import_files"; 
$PART_SIZE = 204800;


`zip -r $ZIP_FILENAME $FILES_FILENAME $OFFERS_FILENAME $IMPORT_FILENAME`;

open(A, $ZIP_FILENAME);

http_query("catalog","checkauth");
http_query("catalog","init");


$counter = 1;
while(!eof(A)){
    read A, $partdata, $PART_SIZE;
    $temp_file = "zip.$counter.part";
    open(B,">$temp_file");
    print B $partdata;
    close(B);
    print "ZIP upload(part $counter): ".http_query("catalog","file",$ZIP_FILENAME, $temp_file)."\n";
    unlink($temp_file);
    $counter++;
}
unlink($ZIP_FILENAME);
close(A);

while(($answer =  http_query("catalog","import",$IMPORT_FILENAME)) eq 'progress'){
    print "$IMPORT_FILENAME : $answer\n";
}
print "$IMPORT_FILENAME : $answer\n";


while(($answer =  http_query("catalog","import",$OFFERS_FILENAME)) eq 'progress'){
    print "$OFFERS_FILENAME : $answer\n";
}
print "$OFFERS_FILENAME : $answer\n";



sub http_query{
    $type           = shift;
    $mode           = shift;
    $filename       = shift;
    $data_filename  = shift;
    
    @stat = stat($data_filename) if -e $data_filename;
    $size = $stat[7];
    
    $url = "http://$DOMAIN/bitrix/admin/1c_exchange.php?type=$type&mode=$mode&filename=$filename";
    
    $command = "wget ";
    $command .=" -o /dev/null"; 
    $command .=" -O /dev/stdout "; 
    $command .=" --post-file=$data_filename" if -e $data_filename;
    $command .=" --header='Cookie:PHPSESSID=$PHPSESSID'";
    $command .=" --header='Content-Type:application/octet-stream'" if -e $data_filename;;
    $command .=" --header='Content-Length:$size'" if -e $data_filename;;
    $command .= " '$url'";
    
    $ans = `$command`;
    
    $ans = $1 if $ans=~m/^\s*([^\s]+)\s*.*$/im;
    
    return $ans;
}

