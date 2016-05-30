=encoding UTF-8

=head1 Report

Класс для работы с отчетами

=head2 Методы

=cut
package Reports;

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
        # Информация о пройденных модульных тестах
        $self->{unittests} = {};
        $self->{unittests} = {};
        bless $self,$class;
        
        return $self;
    }

=head3 addUnittestSection($data)

    Добавление секции модульных тестов
    
=item * B<$data> - вывод phpunit в формате TAP

=back
=cut
    sub addUnittestSection{
        my ($self,$data) = @_;
        
    }
    
    
1;
