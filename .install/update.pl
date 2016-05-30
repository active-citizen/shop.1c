#!/usr/bin/perl

use strict;
use File::Basename;

# Определяем базовый каталог работы инсталлера
my $rel_path = $0;      chomp($rel_path);
my $start_path = `pwd`; chomp($start_path);


chdir($start_path);chdir(dirname($rel_path));

my $base_path = `pwd`;  chomp($base_path);

chdir($base_path);

# ПУть к пользовательским модулям
use lib ("modules");

# Подключение системных модулей
use DBI;
use Getopt::Long;

# Подключение своих модулей
require Dialog;
require Conf;
require Git;
require Migration;
require Unittests;
require Report;

# Аргументы, получаемые из командной строки
my $ARG_VERBOSE	                =   0;
my $ARG_HELP                    =   0;
my $ARG_INI_FILE                =   '';
my $ARG_SHOW_DEFAULT_CONFIG     =   0;
my $UNITTESTS                   =   0;
my $SYNC                        =   0;
my $MAKEREPORT                  =   0;
my $SENDREPORT                  =   0;

# Получение аргументов командной строки и помещение их в соответствующие переменные
GetOptions (
    "verbose"  	            =>  \$ARG_VERBOSE,
    "help"                  =>  \$ARG_HELP,
    "config=s"	            =>  \$ARG_INI_FILE,
    "show-template-config"  =>  \$ARG_SHOW_DEFAULT_CONFIG,
    "unittests"             =>  \$UNITTESTS,
    "sync"                  =>  \$SYNC,
    "make-report"           =>  \$MAKEREPORT,
    "send-report"           =>  \$SENDREPORT
);

# Вывод помощи по ключу командной строки
Dialog::ShowHelp()     if $ARG_HELP;
# Вывод шаблонного конфига по ключу командной строки
Conf::ShowTemplateConfig()  if $ARG_SHOW_DEFAULT_CONFIG;

#die("Update is locked") if Dialog::isLock();
Dialog::setLock() unless Dialog::isLock();

# Получаем настройки
my $conf = Conf->new($ARG_INI_FILE);
Dialog::FatalError($conf->{error}) if $conf->{error};
$conf->set("System::base_path",$base_path);

# Синхронизация кода из репозитория
# и запуск миграций в случае обновления кода
my $git = Git->new($conf, $ARG_VERBOSE);
if($SYNC){
    $git->sync() ;

    # тестовые коммиты для проверки
    #$git->{last_commit} = "2f73d1ed14860dbb8e7899b5e4f2ab0f18aed3d9";
    #$git->{new_commit}  = "95b883cffa6fe7d2a61a6aa9ee0ea3cee1e2fc7e";

    if($git->{last_commit} ne $git->{new_commit}){
        my $migration = Migration->new($conf, $ARG_VERBOSE);
        $migration->execDiff($git->{last_commit},$git->{new_commit});
    }
}

if($UNITTESTS){
    my $unittests = Unittests->new($conf, $ARG_VERBOSE);
    Dialog::FatalError("Некоторые модульные тесты провалены\n\n\n".$unittests->{error}) unless $unittests->go();
    
}



Dialog::resetLock();


