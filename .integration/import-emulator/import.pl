#!/usr/bin/perl 

$ZIP_FILENAME = "v8_BD31_50.zip";
$IMPORT_FILENAME = "import9_23_14_12_4.xml";
$OFFERS_FILENAME = "offers9_23_14_12_4.xml";
$FILES_FILENAME = "import_files"; 
$PART_SIZE = 204800;

`zip -r $ZIP_FILENAME $FILES_FILENAME $OFFERS_FILENAME $IMPORT_FILENAME`;

open(A, $ZIP_FILENAME);

$counter = 1;
while(!eof(A)){
    read A, $partdata, $PART_SIZE;
    open(B,">zip.$counter.part");
    print B $partdata;
    close(B);
    $counter++;
}
close(A);

