
=encoding UTF-8

=head1 Conf 

Класс для работы с конфигом установщика магазина поощрений Активный Гражданин

=head2 Методы

=head3 new($conffile)

=over 4

=item * B<$conffile> - имя файла с конфигурацией установщика
Если файл не указан, берётся F<config.ini> из корня установщика

=back
=cut
package Conf; 

use base Common;

use strict;

    sub new{
        my ($class,$conffile) = @_;
        # Имя файла пользовательсного конфига по умолчанию
        $conffile = "./config.ini" unless $conffile;
        
        # Атрибуты класса
        my $self = Common::new($class);
        $self->{"data"} = {};

        # Проверка существовани пользовательского конфига
        unless(-e $conffile){
            $self->{error}="File $conffile not exists";
            return $self;
        };
        
        use Config::IniFiles;
        # Читаем пользовательский конфиг
        my $current_conf = Config::IniFiles->new( -file=> $conffile );
        # Читаем конфиг по-умолчанию в конце этого файла
        my $default_conf = Config::IniFiles->new( -file=> \*DATA);
    
        # Формируем хэш $self->{data} из конфига с ключами вида
        # Секция::параметр. Чего в пользовательском конфиге нет -
        # заполняем из конфига по-умолчанию
        foreach my $section($default_conf->Sections){
            foreach my $parameter($default_conf->Parameters($section)){
                
                $self->{data}->{"$section::$parameter"} =  
                    $current_conf->val($section,$parameter,$default_conf->val($section,$parameter));
                    
                # Прекращаем работу, если какой-то из параметров не указан ни в конфиге по умолчанию
                # ни в пользовательском конфиге
                unless($self->{data}->{"$section::$parameter"}){
                    $self->{error}="Parameter $section::$parameter must be defined";
                    return $self;
                }
                
            }
        }
        
        
        bless($self, $class);
        return $self;
    }


=head3 get($parameter)

Получение значения параметра из текстового конфига

=over 4

=item * B<$parameter> - Название параметра в формате I<Секция::параметр>, 
например I<Bitrix::admin_login>

=back


=cut
    sub get{
        my ($self, $name) = @_;
        my $value = $self->{data}->{$name};
        
        if($name eq "System::temp_dir"){
            unless(-e $value){
                Dialog::FatalError("Не могу создать временный каталог $value")
                    unless mkdir($value);
            }
            
            Dialog::FatalError("Не удалось создать временный каталог $value")
                unless -e $value || -w $value;
        }
        return $value;
    }

=head3 set($parameter,$value)

Установка значения параметра

=over 4

=item * B<$parameter> - Название параметра в формате I<Секция::параметр>, 
например I<Bitrix::admin_login>

=item * B<$value> - значение параметра

=back
=cut
    sub set{
        my ($self, $name, $value) = @_;
        $self->{data}->{$name} = $value;
    }
    
    

=head3 ShowTemplateConfig()

Вывод текста шаблонного конфига для последующего формирования из него рабочего

=cut
    sub ShowTemplateConfig{
        my $answer = '';
        my $line = '';
        my $programm = '';
        my $path = '';
        my $default = '';
        while(<DATA>){
            $line = $_;
            
            # Заполняем системные пути для ключей с whereis_
            if($_=~m/^\s*whereis_(.*?)\s*\=\s*(.*)\s*.*$/){
                $programm = $1;
                $default = $2;
                chomp($default);
                # Пропускаем непустые значения
                $answer = `whereis $programm`;
                chomp($answer);
                $path = $1 if $answer=~m/^\s*$programm:\s*([\d\w\-\_\%\/\.\,\~]+)\s*.*$/gi && !$default;
                $path = $default if $default;
                print "whereis_$programm = $path \n";
            }
            else{
                print $line;
            }
        }
        print join "", <DATA>;
        exit(0);
    }





1;

# Ниже конфиг по умолчанию
__DATA__
################################################################################
#  Конфигурационный файл установщика магазина поощрений Активный Гражданин
################################################################################
[Project]
name = 

[System]
# Пути к необходимым для установки утилитам
whereis_wget = 
whereis_git = 
whereis_unzip = 
whereis_rm = 
whereis_mv = 
whereis_rsync = 
whereis_php = 
temp_dir = 
phantonjs_path = bin/phantomjs
phpunit_path = bin/phpunit

[Bitrix]

# Прямая ссылка на архив дистрибутива Битрикса в формате tar.gz
download_url = http://www.1c-bitrix.ru/download/business_encode_php5.zip
# Имя пользователя админитратора Битрикс
admin_login = admin
# Пароль администратора Битрикс
admin_password = 
# Email администратора
admin_email = 
# Код пердустанавлеваемого решения (ID радиокнопки в установщике)
# - id_radio_bitrix.sitecorporate:bitrix:corp_furniture
# - id_radio_bitrix.sitecorporate:bitrix:corp_services
# - id_radio_bitrix:demo
# - id_radio_bitrix.sitecommunity:bitrix:demo_community
# - id_radio_bitrix.sitepersonal:bitrix:demo_personal
# - id_radio_bitrix.eshop:bitrix:eshop
# - id_radio_bitrix.siteinfoportal:bitrix:infoportal
solution_code = id_radio_bitrix.eshop:bitrix:eshop


[Git]

# Ссылка на репозиторий с которого будет создан клон
repository_url = git@github.com:active-citizen/shop.1c.git
# Ветка, из которой будет всё слито
branch = master

[Hosting]

db_port = 3306 
db_host = 
db_user = 
db_pass = 
db_name =
http = 

[Report]
# Каталог для отчетов
folder = reports
# Адресаты для получения отчетов
receivers = andrey.inyutsin@altarix.ru


[Mail]
smtp_host = localhost
smtp_port = 465
smtp_ssl = tsl
smtp_login = username
smtp_password = password



