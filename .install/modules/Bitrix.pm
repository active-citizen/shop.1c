=encoding UTF-8

=head1 Bitrix 

Класс для работы с Bitrix-дистрибутивом

=head2 Методы

=head3 new($conf, $verbose)

=over 4

=item * B<$conf> - ссылка на объект класса Conf (настройки установщика)

=item * B<$verbose> - флаг болтливого режима


=back
=cut


package Bitrix;

    sub new{
        
        my ($class, $conf, $verbose) = @_;
        
        my $self = {
    	    "verbose"	=>  $verbose,
    	    "conf"	    =>  $conf
        };
        bless $self,$class;
        
        return $self;
    }

=head3 install()

Установка дистрибутива Битрикс

=cut
    
    sub install{
        my ($self) = @_;
        #$self->downloadDist();
        $self->clearFiles();
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
            " -O ".$self->{conf}->get("System::temp_dir")."/bitrix.tar.gz".     
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
        chdir("..");
        opendir(my $dd, ".");
        my $filename = '';
        AAA:while($filename = readdir($dd)){
            chomp($filename);
            next AAA if $filename eq '.' || $filename eq '..';
            # Не чистим элементы из @not_delete
            next AAA if grep /$filename/,@not_clear;
            $command = $self->{conf}->get("System::whereis_rm")." -fr ".$filename;
            print "\n".$command if $self->{verbose};
            CLI::Dialog::FatalError("Не могу удалить $filename") if `$command`;
        }
        print "[Ok]\n";
        chdir(".install");
    }

=head3 clearDatabase()

Очистка базы данных сайта под новую установку

=cut
    sub clearDatabase{
        my ($self) = @_;
        
    }



1;
