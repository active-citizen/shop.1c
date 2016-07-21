=encoding UTF-8

=head1 Bitrix 

Класс для работы с Bitrix-дистрибутивом

=head2 Методы

=cut
package Bitrix;
use base Common;


=head3 new($conf, $verbose)

=over 4

=item * B<$conf> - ссылка на объект класса Conf (настройки установщика)

=item * B<$verbose> - флаг болтливого режима


=back
=cut
    sub new{
        
        my ($class, $conf, $verbose) = @_;
        
        my $self = Common::new($class, $conf, $verbose);
        bless $self,$class;
        
        return $self;
    }

=head3 install()

Установка дистрибутива Битрикс

=cut
    
    sub install{
        my ($self) = @_;
        $self->downloadDist();
        $self->clearFiles();
        $self->unpack();
        $self->checkDist();
        $self->clearDatabase();
        $self->installFromBrowser();
    }
    
=head3 downloadDist()

Скачивание дистрибутива Битрикс во временый каталог

=cut
    sub downloadDist{
        my ($self) = @_;
        print "Загрузка дистрибутива Битрикс ";
        # Формируем команду на скачивание
        my $command = 
            $self->{conf}->get("System::whereis_wget").
            # Куда сохраняем скачанный архив
            " -O ".$self->{conf}->get("System::temp_dir")."/bitrix.zip".     
            # Вывод ошибок в STDOUT
            " -o /dev/stdout ".                                                 
            # Процесс загрузки для болтливого режима
            " --progress=dot:mega ".
            # Url загрузки
            " \"".$self->{conf}->get("Bitrix::download_url")."\"";
        # печатаем команду для полтливого режима
        print "\n\$".$command."\n" if $self->{verbose};
        # Открываем PIPE
        open(WGET, "$command|");
        # Тянем из PIPE данные, выводя их в болтливом режиме
        while(<WGET>){print $_ if $self->{verbose}; }
        Dialog::FatalError("Архив не скачан ") unless  
            -e $self->{conf}->get("System::temp_dir")."/bitrix.zip" 
            &&
            -s $self->{conf}->get("System::temp_dir")."/bitrix.zip"; 
        print "[OK]\n";
    }

=head3 clearFiles()

Очистка корневого каталога сайта под новую установку

=cut
    sub clearFiles{
        my ($self) = @_;
        # Список элементов корневого каталога, которые не надо чистить
        @not_clear = qw(.install .git);

        my $command = '';
        print "Очистка корневого каталога сайта перед установкой ";
        # Перечисляем каталоги, которые не надо удалять
        chdir($self->{conf}->get("System::base_path")."/..");
        opendir(my $dd, ".");
        my $filename = '';
        AAA:while($filename = readdir($dd)){
            chomp($filename);
            next AAA if $filename eq '.' || $filename eq '..';
            # Не чистим элементы из @not_delete
            next AAA if grep /$filename/,@not_clear;
            $command = $self->{conf}->get("System::whereis_rm")." -fr ".$filename;
            print "\n".$command if $self->{verbose};
            Dialog::FatalError("Не могу удалить $filename") if `$command`;
        }
        print "[Ok]\n";
        chdir($self->{conf}->get("System::base_path"));
    }

=head3 clearDatabase()

Очистка базы данных сайта под новую установку

=cut
    sub clearDatabase{
        my ($self) = @_;

        print "Очистка БД ";
        print "\nПодключение к БД " if $self->{verbose};
        my $connection = 
            "DBI:mysql:".
            $self->{conf}->get("Hosting::db_name").":".
            $self->{conf}->get("Hosting::db_host").":".
            $self->{conf}->get("Hosting::db_port");
        Dialog::FatalError("Не удаётся подключиться к БД с указанными в настройках параметрами") 
            unless my $dbh =  DBI->connect(
                $connection,
                $self->{conf}->get("Hosting::db_user"),
                $self->{conf}->get("Hosting::db_pass")
            );
            
        my $stha = $dbh->prepare("SHOW TABLES;");
        Dialog::FatalError($DBI::errstr) unless $stha->execute;

        my $sql = '';
        CAB:while(my $table=$stha->fetchrow_array){
	    next CAB unless $table=~m/^b_/i;
            $sql = "DROP TABLE `$table`;";
            print $sql."\n" if $self->{verbose};
            Dialog::FatalError($DBI::errstr) unless $dbh->do($sql);
        }

        print "[Ok]\n";
    }

=head3 unpack()

Распаковка скачанного архива Битрикс в корень сайта

=cut
    sub unpack{
        my ($self) = @_;
        print "Распаковка архива с дистрибутивом Битрикс  ";
        my $command = '';

        $command = $self->{conf}->get("System::whereis_mv")." ".
            $self->{conf}->get("System::temp_dir")."/bitrix.zip ".$self->{conf}->get("System::base_path")."/..";
        `$command`;

        chdir($self->{conf}->get("System::base_path")."/..");
        
        $command = $self->{conf}->get("System::whereis_unzip")." bitrix.zip";
        `$command`;
        print "\n".$command if $self->{verbose};
        

        chdir($self->{conf}->get("System::base_path"));
        
        print "[Ok]\n";
    }

=head3 checkDist()

Проверка распакованного дистрибутива

=cut
    sub checkDist{
        my ($self) = @_;
        print "\nПроверка содержимого архива  ";
        # Ожидаемое содержимое архива (для проверки распаковки)
        my @archive_content = qw(
            .access.php
            bitrix
            .htaccess
            index.php
            .install
            install.config
            license.html
            license.php
            readme.html
            readme.php
            upload
            web.config
        );
        
        chdir($self->{conf}->get("System::base_path")."/..");
        # Проверка содержимого архива
        my $fail_flag = 0;
        foreach my $filename(@archive_content){
            Dialog::FatalError("Путь <$filename> после распаковки не обнаружен")
                unless -e $filename;
        }
        chdir($self->{conf}->get("System::base_path"));
        print "[Ok]\n";
    }

    
=head3 installFromBrowser()

Установка из браузера

=cut
    sub installFromBrowser{
        my ($self) = @_;
        $phantomjs=Phantomjs->new($self->{conf},$self->{verbose});
        my $code = $phantomjs->generateJS();
        
        my $js_script = '';
        $js_script = $self->{conf}->get("System::temp_dir")."/install.js";
        
        open(A, ">".$js_script);
        print A $code;
        close(A);
        
        Dialog::FatalError("Не удалось создать установочный скрипт $js_script")
            unless -e $js_script;
        
        my $command = $self->{conf}->get("System::phantonjs_path")." --cookies-file=".$self->{conf}->get("System::temp_dir")."/bitrix.cookie.txt ".$js_script;
        
        open(A, "$command|") or 
            Dialog::FatalError("Не удалось запустить установочный скрипт через phantomjs");
            
        while(<A>){
            print $_;
            Dialog::FatalError("Ошибка выполнения js-установщика: ".$_)
                if $_=~m/ERROR/;
        }
        
    }
    
1;
