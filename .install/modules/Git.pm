=encoding UTF-8

=head1 Bitrix 

Класс для работы с Git

=head2 Методы

=cut
package Git;


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

=head3 pull()

=cut
    
    sub checkout{
        my ($self) = @_;
    }
    
    
    
    
1;
