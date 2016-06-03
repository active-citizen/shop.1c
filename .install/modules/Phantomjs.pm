=encoding UTF-8

=head1 Phantomjs

Класс для работы с Phantomjs

=head2 Методы

=cut
package Phantomjs;

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
    
=head3 generateJS()

Генерация исполняемого кода для phantomjs

=cut
    sub generateJS{
        my ($self) = @_;
        
        # Берём основу js-кода в шаблонном файле
        open(A,$self->{conf}->get("System::base_path")."/js/template.js");
        my $jscode = join("",<A>);
        close(A);
        
        # Формируем список фалов вида 001-*.js, 002-*.js и.т.д. для формирования 
        # этапов установки
        opendir(my $dd,"js");
        my @scripts = ();
        AAA:while(my $filename = readdir($dd)){
            chomp($filename);
            push @scripts,$filename if $filename=~m/(\d+)\-/g;
        }
        @scripts = sort @scripts;
        
        # Формируем js-код этапов 
        my $datacode = '';
        foreach my $filename(@scripts){
            open(A,"js/".$filename);
            $datacode .= "\n/* $filename */\ndatas.push(".join("",<A>).");";
            close(A);
        }
        $datacode .= "\n";
        
        # Заменяем код
        $jscode=~s/{Install:data}/$datacode/gm;

        # Вставляем ключи из настроек
        my $value = "";
        foreach my $key($jscode=~m/\{\{(.*?)\}\}/gim){
            $value = $self->{conf}->get($key);
            $jscode=~s/\{\{$key\}\}/$value/gim;
        }
        
        # Убираем скриншоты
        $jscode=~s|/\*screenshot\*/.*?/\*end screenshot\*/||gim
            unless $self->{conf}->get("Bitrix::make_screenshots") eq 'yes';
        
        # Убираем болтливые режимы
        $jscode=~s|/\*verbose\*/.*?/\*end verbose\*/||gim
            unless $self->{verbose};

        return $jscode;
    }


1;
