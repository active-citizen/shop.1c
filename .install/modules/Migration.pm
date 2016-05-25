=encoding UTF-8

=head1 Migration 

Класс для работы с миграциями

=head2 Методы

=cut
package Migration;


=head3 new($conf, $verbose)

=over 4

=item * B<$conf> - ссылка на объект класса Conf (настройки установщика)

=item * B<$verbose> - флаг болтливого режима


=back
=cut 
    sub new{
        
        my ($class, $conf, $verbose) = @_;
        
        my $self = {
    	    "verbose"	=>  $verbose,
    	    "conf"	    =>  $conf
        };
        bless $self,$class;
        
        return $self;
    }

=head3 sync()

Синхронизация файлов из репозитория. Возвращает истину, если файлы были обновлены

=cut
    
    sub sync{
        my ($self) = @_;
    }
    
1;