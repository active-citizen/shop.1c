#!/usr/bin/perl
use strict;
use lib qw(modules/CPAN modules/My);

use Getopt::Long;
use File::Basename;
use CLI::Dialog;
use Options;

# Аргументы, получаемые из командной строки
my $ARG_INTERACTIVE =   0;
my $ARG_VERBOSE	    =   0;
my $ARG_HELP        =   0;

# Получение аргументов командной строки и помещение их в соответствующие переменные
GetOptions (
    "interactive"   => \$ARG_INTERACTIVE,      # string	
    "verbose"  	    => \$ARG_VERBOSE,
    "help"          => \$ARG_HELP
);

# Если вывод справки - печатаем её и выходим
CLI::Dialog::show_help() && die if $ARG_HELP;

# Получение опций установщика
my %OPTS = %Options::OPTS;
# Заполнение опций установщика в интерактивном режиме если в командной строке 
# указано --interactive
%OPTS = %{CLI::Dialog::interactive(\%OPTS)} if $ARG_INTERACTIVE;



print $_."=>".$OPTS{$_}."\n" foreach keys %OPTS;
