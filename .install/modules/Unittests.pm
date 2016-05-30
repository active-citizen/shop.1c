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
        $self->{total} = '';
        bless $self,$class;
        
        return $self;
    }

=head3 go()

    Выполнение unit-тестов. Возвращает 1, если все пройдены успешно.
    Возвращает 0, если какие-то тесты провалились и текст ошибки в $self->{error}
    
=cut
    sub go{
        my ($self) = @_;
        
        my $command = $self->{conf}->get("System::phpunit_path")." --bootstrap bin/bootstrap.phpunit.php -v --tap ../.unittests";
        
        
        my $result = $self->shell($command);
        
        @lines = split("\n",$result);
        
        my $open_flag = 0;
        my $line = '';
        my $total = '';
        foreach $line(@lines){
            $open_flag = 1 if $line=~m/not ok \d+/;
            $self->{error} .= $line."\n" if $open_flag;
            if($line=~m/\.\.\./){
                $open_flag = 0; 
            }
            $self->{total} = $line;
        }
        return 0 if $self->{error};
        return 1;
    }
    
    
1;
