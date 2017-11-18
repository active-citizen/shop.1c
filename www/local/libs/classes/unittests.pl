#!/usr/bin/perl
# Скрипт для запуска unit-тестов
# 

use Cwd;
use Cwd 'abs_path';
use File::Basename;

$workDir = getcwd();

$sCurrentPath =  __FILE__;
chdir(dirname($sCurrentPath));
$sCurrentPath = abs_path($sCurrentPath);
$CWD = getcwd();


@testsFolders = `find $CWD -type d -name tests`;

$phpunitPath = abs_path($CWD."/../bin/phpunit.phar");

if($workDir ne $CWD){
        $command = "$phpunitPath --bootstrap $CWD/bootstrap.php --color=always $workDir";
        print `$command`;
}
else{
    foreach $testFolder(@testsFolders){
        $command = "$phpunitPath --bootstrap $CWD/bootstrap.php --color=always $testFolder";
        print `$command`;
    }
}





#/phpunit-5.7.phar \
#   --bootstrap bootstrap.php \
#   --color=always \
#   --debug \
#   dev-tests





