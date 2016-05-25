#!/usr/bin/perl

use strict;

my $rel_path = $0;
my $start_path = `pwd`;
chomp($rel_path);chomp($start_path);
my $base_path = `pwd`;
chomp($base_path);

print "|$base_path|";
die;

use lib ("$base_path modules");

use File::Basename;
use DBI;
use Getopt::Long;
use Dialog;
use Conf;
use Git;
use Migration;

# Аргументы, получаемые из командной строки
my $ARG_VERBOSE	                =   0;
my $ARG_HELP                    =   0;
my $ARG_INI_FILE                =   '';
my $ARG_SHOW_DEFAULT_CONFIG     =   0;
my $UNITTESTS                   =   0;

# Получение аргументов командной строки и помещение их в соответствующие переменные
GetOptions (
    "verbose"  	            => \$ARG_VERBOSE,
    "help"                  => \$ARG_HELP,
    "config=s"	            => \$ARG_INI_FILE,
    "show-template-config"  => \$ARG_SHOW_DEFAULT_CONFIG,
    "unittests"             => \$UNITTESTS,
);

# Вывод помощи по ключу командной строки
Dialog::ShowHelp()     if $ARG_HELP;
# Вывод шаблонного конфига по ключу командной строки
Conf::ShowTemplateConfig()  if $ARG_SHOW_DEFAULT_CONFIG;

# Получаем настройки
my $conf = Conf->new($ARG_INI_FILE);
Dialog::FatalError($conf->{error}) if $conf->{error};

# Синхронизация кода из репозитория
# и запуск миграций в случае обновления кода
my $git = Git->new($conf, $ARG_VERBOSE);
$git->sync();


$git->{last_commit} = "3215e42dd6a8259d979b354ddd3a3262492ac152";
$git->{new_commit}  = "95b883cffa6fe7d2a61a6aa9ee0ea3cee1e2fc7e";

if($git->{last_commit} ne $git->{new_commit}){
    my $migration = Migration->new($conf, $ARG_VERBOSE);
    $migration->execDiff($git->{last_commit},$git->{new_commit});
}




