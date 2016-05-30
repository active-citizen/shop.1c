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

        my @migrations = $self->diff($last_commit, $new_commit);
        
        chdir($self->{conf}->get("System::base_path")."/..");
        
        foreach my $migration(@migrations){
            $command = $self->{conf}->get("System::whereis_php")." $migration";
            print $command."\n";
        }
        
        chdir($self->{conf}->get("System::base_path"));
    }
    

=head3 diff()

Получение списка добавленных файлов миграций между текущим коммитом и предыдущими

=cut
    
    sub diff{
        my ($self,$last_commit, $new_commit) = @_;
        
        my $git = Git->new($self->{conf}, $self->{verbose});
        # Получаем список новых файлов между ревизиями по маске
        chdir($self->{conf}->get("System::temp_dir")."/git");
        my @migrations = $git->new_files($last_commit, $new_commit,"\\.mig\$");
        return @migrations;
    }
    
    
    
    
    
    
    
    
1;