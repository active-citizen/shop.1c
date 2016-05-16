#!/usr/bin/perl
use strict;
use File::Basename;
use lib (dirname($0)."/modules/CPAN", dirname($0)."/modules/My");

use Getopt::Long;
use CLI::Dialog;
use Conf;
use Bitrix;

# Аргументы, получаемые из командной строки
my $ARG_VERBOSE	                =   0;
my $ARG_HELP                    =   0;
my $ARG_INI_FILE                =   '';
my $ARG_SHOW_DEFAULT_CONFIG     =   0;

# Получение аргументов командной строки и помещение их в соответствующие переменные
GetOptions (
    "verbose"  	            => \$ARG_VERBOSE,
    "help"                  => \$ARG_HELP,
    "config=s"	            => \$ARG_INI_FILE,
    "show-template-config"  => \$ARG_SHOW_DEFAULT_CONFIG,
);

# Вывод помощи по ключу командной строки
CLI::Dialog::ShowHelp()     if $ARG_HELP;
# Вывод шаблонного конфига по ключу командной строки
Conf::ShowTemplateConfig()  if $ARG_SHOW_DEFAULT_CONFIG;

# Получаем настройки
my $conf = Conf->new($ARG_INI_FILE);
CLI::Dialog::FatalError($conf->{error}) if $conf->{error};

my $bitrix = Bitrix->new($conf, $ARG_VERBOSE);
$bitrix->install();
