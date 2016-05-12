####################################
#   
#
####################################
package Conf;
use strict;


=pod
    Конструктор класса Conf. Читает конфгурационный файл.
    
    Использование
    $Conf = Conf->new(
        "config.ini",   # Имя файла-конфига
        0               # Флаг болтливого режима
    );
    
=cut
sub new{
    
    my ($class, $conffile, $verbose) = @_;
    $conffile = "./config.ini" unless $conffile;
    
    my $self = {
        "error"     =>  "",
        "cfg"       =>  undef,
        "verbose"   =>  $verbose,
        "data"      =>  {}
    };
    
    bless $self, $class;
    
    use Config::IniFiles;
    my $self->{cfg} = Config::IniFiles->new( -file=>$conffile);

    # Значения настроек по умолчанию
    my $self->{defaults} = \(
        "Bitrix::download_url"  =>  "http://www.1c-bitrix.ru/download/business_encode_php5.tar.gz",
        "Bitrix::admin_login"   =>  "admin",
        "Bitrix::admin_password"=>  "",
        "Git::repository_url"   =>  "git\@github.com:active-citizen/shop.1c.git",
        "Git::branch"           =>  "master"
    );
    
    
    return $self;
}

1;
