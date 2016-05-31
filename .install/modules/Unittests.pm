=encoding UTF-8

=head1 Unittests

Класс для работы с юнит-тестами

=head2 Методы

=cut
package Unittests;

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
        $self->{ok} = ();
        $self->{notok} = ();
        $self->{failures} = ();
        bless $self,$class;
        return $self;
    }

=head3 go()

    Выполнение unit-тестов. Возвращает 1, если все пройдены успешно.
    Возвращает 0, если какие-то тесты провалились
    
    Кроме того следующие аттрибуты класса содержат:

=over 4

=item * B<ok> - список пройденных тестов

=item * B<notok> - список заваленных тестов

=item * B<failures> - подробное описание заваленных тестов

=back
    
    
=cut
    sub go{
        my ($self) = @_;
        
        my $command = $self->{conf}->get("System::phpunit_path")." --bootstrap bin/bootstrap.phpunit.php -v --tap ../.unittests";
        
        my $result = $self->shell($command);
        
        @lines = split("\n",$result);
        
        my $open_flag = 0;
        my $line = '';
        my $total = '';
        my $error = '';
        foreach $line(@lines){
            $open_flag = 1 if $line=~m/not ok \d+/;
            $error .= $line."\n" if $open_flag;
            push @{$self->{ok}}, $1 if $line=~m/^ok\s*\d+\s*\-\s*(.*)/i;
            push @{$self->{notok}}, $1 if $line=~m/^not\s*ok\s*\d+\s*\-\s*Failure:\s*(.*)/i;
            if($line=~m/\.\.\./){
                push @{$self->{failures}}, $error;
                $open_flag = 0; 
                $error = '';
            }
            
        }
        
        return 0 if scalar(@{$self->{failures}});
        return 1;
    }
    
=head3 report($type)
    Создание отчета. Возвращает код отчета

=over 4

=item * B<$type> - формат отчета (md - по умолчанию) 

=back

=cut
    sub report{
        my ($self) = @_;
        
        my $code = '';
        my $oks = scalar(@{$self->{ok}});
        my $notoks = scalar(@{$self->{notok}});
        
        my $code    =   "\n# Unit-тестирование".($notoks?" (пройдено с ошибками)":"")."\n";
        $code       .=  "\n* Неуспешных тестов тестов: ".int($notoks).";"; 
        $code       .=  "\n* Успешных тестов: ".int($oks).";"; 
        $code       .=  "\n";
        my @lines = ();
        
        if(scalar(@{$self->{failures}})){
            $code   .=  "\n## Подробно о неуспешных тестах\n";
            foreach my $failure(@{$self->{failures}}){
                @lines = split("\n",$failure);
                chomp(@lines);
                $code .="\n    $_  " foreach @lines;
                $code .="\n";
            }
        }

        $self->{report} = $code;
        return $code;
    }
    
    

    
    
    
1;
