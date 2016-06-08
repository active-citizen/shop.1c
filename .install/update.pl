#!/usr/bin/perl

# Подключение системных модулей
use strict;
use File::Basename;
use DBI;
use Getopt::Long;

# Определяем базовый каталог работы инсталлера и переходим в него
my $rel_path = $0;      chomp($rel_path);
my $start_path = `pwd`; chomp($start_path);
chdir($start_path);     chdir(dirname($rel_path));
my $base_path = `pwd`;  chomp($base_path);
chdir($base_path);

# ПУть к пользовательским модулям
use lib ("modules");

# Подключение своих модулей
require Dialog;
require Conf;
require Git;
require Migration;
require Report;
require Unittests;
require CodeQuality;
require Bitrix;
require Phantomjs;

# Аргументы, получаемые из командной строки
my $ARG_VERBOSE	                =   0;
my $ARG_HELP                    =   0;
my $ARG_INI_FILE                =   '';
my $ARG_SHOW_DEFAULT_CONFIG     =   0;
my $UNITTESTS                   =   0;
my $SYNC                        =   0;
my $MAKEREPORT                  =   0;
my $MIGRATIONS                  =   0;
my $SENDREPORT                  =   0;
my $BITRIX_INSTALL              =   0;
my $SHOWQUALITY                 =   0;
my $FORCEUNLOCK                 =   0;

# Получение аргументов командной строки и помещение их в соответствующие переменные
GetOptions (
    "verbose"  	            =>  \$ARG_VERBOSE,
    "help"                  =>  \$ARG_HELP,
    "config=s"	            =>  \$ARG_INI_FILE,
    "install-bitrix"        =>  \$BITRIX_INSTALL,
    "show-template-config"  =>  \$ARG_SHOW_DEFAULT_CONFIG,
    "unittests"             =>  \$UNITTESTS,
    "sync"                  =>  \$SYNC,
    "migrations=s"          =>  \$MIGRATIONS,
    "make-report"           =>  \$MAKEREPORT,
    "send-report"           =>  \$SENDREPORT,
    "show-quality"          =>  \$SHOWQUALITY,
    "force-unlock"          =>  \$FORCEUNLOCK,
);

# Вывод помощи по ключу командной строки
Dialog::ShowHelp()     if $ARG_HELP;
# Вывод шаблонного конфига по ключу командной строки
Conf::ShowTemplateConfig()  if $ARG_SHOW_DEFAULT_CONFIG;

# Стоп, если один экземпляр уже выполняется
Dialog::FatalError("Работа скрипта заблокирована. 
    Он уже выполняется или предыдущее выполнение закончилось аварийно.
    Вы можете снять блокировку, запустив скрипт с ключом --force-unlock",!$FORCEUNLOCK) if Dialog::isLock();
Dialog::setLock() unless Dialog::isLock();

# Получаем настройки
my $conf = Conf->new($ARG_INI_FILE);
Dialog::FatalError($conf->{error}) if $conf->{error};
$conf->set("System::base_path",$base_path);

Dialog::FatalError("Ключ --migration=a:b не доступен с ключом --sync") if $SYNC && $MIGRATIONS=~m/[a-f0-9]+:[a-f0-9]+/;
Dialog::FatalError("Ключ --migration=all доступен только с ключом --sync") if !$SYNC && $MIGRATIONS eq 'all';


# Устанавливаем битрикс, если установка
if($BITRIX_INSTALL){
    my $bitrix = Bitrix->new($conf, $ARG_VERBOSE);
    $bitrix->install();
}

# Синхронизация кода из репозитория
# и запуск миграций в случае обновления кода
my $git = Git->new($conf, $ARG_VERBOSE);
my $migration = Migration->new($conf, $ARG_VERBOSE);
if($SYNC){
    $git->sync() ;

    # тестовые коммиты для проверки
    #$git->{last_commit} = "2f73d1ed14860dbb8e7899b5e4f2ab0f18aed3d9";
    #$git->{new_commit}  = "95b883cffa6fe7d2a61a6aa9ee0ea3cee1e2fc7e";

    # Запустить миграции между коммитами если предыдущий коммит отличается от текущего и --migration=all
    $migration->execDiff($git->{last_commit},$git->{new_commit}) if $git->{last_commit} ne $git->{new_commit} && $MIGRATIONS eq 'all';
}

# Если нет ключа --sync и указаны начальный и конечный коммит - выполнить миграции между ними
$migration->execDiff($1,$2) if !$SYNC && $MIGRATIONS=~m/([a-f0-9]+):([a-f0-9]+)/;

my $report = Report->new($conf, $ARG_VERBOSE);

# Запустить модульные тесты если --unittests 
if($UNITTESTS){
    my $unittests = Unittests->new($conf, $ARG_VERBOSE);
    # Запустить тесты
    $unittests->go();
    # Сгенерить и добавить модульные тесты в отчет, если --make-report или --seng-report
    $unittests->report() if $SENDREPORT || $MAKEREPORT;
    $report->add($unittests->{report}) if $SENDREPORT || $MAKEREPORT;
}

my $codequality = CodeQuality->new($conf, $ARG_VERBOSE);
$report->add($codequality->report()) if $SENDREPORT || $MAKEREPORT;
# Формируем и/или посылаем отчет если --make-report или --seng-report
$report->send() if $SENDREPORT;
$report->create() if $MAKEREPORT;

# Показать отчет по коду, если --show-report
print "\n".("="x30)." ОТЧЕТ ПО КАЧЕСТВУ КОДА ".("="x30)."\n".$codequality->report(1)."\n".("="x80)."\n" 
    if $SHOWQUALITY;

# Снимаем блокировку
Dialog::resetLock();


