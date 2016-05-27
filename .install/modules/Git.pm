=encoding UTF-8

=head1 Git 

Класс для работы с Git

=head2 Методы

=cut
package Git;

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
        $self->{"last_commit"}  = 0;
        $self->{"new_commit"}   = 0;
        bless $self,$class;
        
        return $self;
    }

=head3 sync()

Синхронизация файлов из репозитория. Возвращает истину, если файлы были обновлены

=cut
    
    sub sync{
        my ($self) = @_;
        my $command = '';
        
        print "Синхронизация из Git-репозитория\n";
        
        chdir($self->{conf}->get("System::temp_dir"));
        
        # Если каталог с временным git-репом не создан - создаём
        mkdir "git" unless -e "git";       
        # Входим в каталог
        chdir("git");
        
        # Если репозиторий там уже есть - обновляем, если нет - клонируем
        if(my $git_version = $self->version()){
            $self->{last_commit} = $self->last_commit();
            $self->pull();
        }else{
            $self->clone();
        }
        Dialog::FatalError("Не удалось создать временный репозиторий") unless $self->version();
        $self->checkout($self->{conf}->get("Git::branch"));
        $self->{new_commit} = $self->last_commit();
        
        # Синхронизируем файлы, если номер коммита обновился
        $self->rsync() if $self->{new_commit} ne $self->{last_commit};
        chdir($self->{conf}->get("System::base_path"));
        return true if $self->{last_commit} ne $self->{new_commit};
        return false;
    }
    
=head3 clone()

Клонирование репозитория из рнпозитория в текущий каталог

=cut

    sub clone{
        my ($self) = @_;
        # Клонируем репозиторий
        my $command = $self->{conf}->get("System::whereis_git")." clone ".$self->{conf}->get("Git::repository_url")." .";
        $self->shell($command);
    }
    

=head3 version()

Получение версии Git текущего каталога(если он там есть)

=cut
    sub version{
        my ($self) = @_;
        
        return 0 unless -e ".git";
        
        my $command = $self->{conf}->get("System::whereis_git")." --version ";

        my $answer = $self->shell($command);
        
        return $1 if $answer=~m/^git\s+version\s+(.*)$/;
        return 0;
    }
    
    
=head3 pull()

Получение версии Git (если он там есть)

=cut
    sub pull{
        my ($self) = @_;
        # Забираем последние изменения
        my $command = $self->{conf}->get("System::whereis_git")." pull ";
        $self->shell($command);
    }
    
=head3 last_commit()

Получение хэша последнего коммита

=cut
    sub last_commit{
        my ($self) = @_;
        my $command = $self->{conf}->get("System::whereis_git")." log ";
        my $hash = '';
        my $answer = $self->shell($command,"no");
        my $answer = (split("\n",$answer))[0];
        $hash = $1 if $answer=~m/^commit\s+([\d\w]+)\s*$/;
        return $hash;
    }


=head3 checkout()

Переключаемся на указанную ветку

=cut
    sub checkout{
        my ($self,$branch) = @_;
        my $command = $self->{conf}->get("System::whereis_git")." checkout $branch ";
        $self->shell($command);
    }

=head3 revert

Откат до указзаного коммита

=cut
    sub reset{
        my ($self,$commit) = @_;
        my $command = $self->{conf}->get("System::whereis_git")." reset --hard $commit ";
        $self->shell($command);
    }
    
    
=head3 rsync()

синхронизация файлов из репозитория

=cut
    sub rsync{
        my ($self) = @_;
        my $command = $self->{conf}->get("System::whereis_rsync")." -av --progress . ".$self->{conf}->get("System::base_path")."/.. --exclude .install";
        $self->shell($command);
    }
    
1;
