=encoding UTF-8

=head1 Migration 

Класс для работы с миграциями

=head2 Методы

=cut
package Migration;

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
    
=head3 execDiff()

выполнение всех новых миграций между коммитами

=cut

    sub execDiff{
        my ($self,$last_commit, $new_commit) = @_;

        chdir($self->{conf}->get("System::temp_dir")." git");
        my $git = Git->new($self->{conf}, $self->{verbose});
        $git->revert($last_commit);
        $git->revert($new_commit);
        
        chdir(dirname($0));
    }
    

=head3 diff()

Получение списка файлов миграций между текущим коммитом и предыдущими

=cut
    
    sub diff{
        my ($self) = @_;
    }
    
1;