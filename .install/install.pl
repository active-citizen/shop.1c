#!/usr/bin/perl
use strict;
use lib qw(modules/CPAN modules/My);

use Getopt::Long;
use File::Basename;
use CLI::Dialog;
use Conf;

# Аргументы, получаемые из командной строки
my $ARG_VERBOSE	    =   0;
my $ARG_HELP        =   0;
my $ARG_INI_FILE    =   '';

# Получение аргументов командной строки и помещение их в соответствующие переменные
GetOptions (
    "verbose"  	    => \$ARG_VERBOSE,
    "help"          => \$ARG_HELP,
    "config=s"	    => \$ARG_INI_FILE,
);
my $Conf = Conf->new($ARG_INI_FILE, $ARG_VERBOSE);


# Если вывод справки - печатаем её и выходим
CLI::Dialog::show_help() && die if $ARG_HELP;
