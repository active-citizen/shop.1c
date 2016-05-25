=encoding UTF-8

=head1 Git 

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
    	    "verbose"	    =>  $verbose,
    	    "conf"	        =>  $conf,
            "last_commit"   =>  0,
            "new_commit"    =>  0
        };
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
        chdir("../..");
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
        open(A, "$command|");
        while(<A>){print $_ if $self->{verbose}};
        close(A);
    }
    

=head3 version()

Получение версии Git (если он там есть)

=cut
    sub version{
        my ($self) = @_;
        
        return 0 unless -e ".git";
        
        my $command = $self->{conf}->get("System::whereis_git")." --version ";
        my $answer = `$command`;chomp($answer);
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
        open(A, "$command|");
        while(<A>){print $_ if $self->{verbose}};
        close(A);
    }
    
=head3 last_commit()

Получение хэша последнего коммита

=cut
    sub last_commit{
        my ($self) = @_;
        my $command = $self->{conf}->get("System::whereis_git")." log ";
        my $hash = '';
        open(A, "$command|");
        AAA:while(<A>){
            print $_ if $self->{verbose};
            chomp($_);
            $hash = $1 if ~m/^commit\s+([\d\w]+)\s*$/;
            last AAA if $hash;
        };
        close(A);
        return $hash;
    }


=head3 checkout()

Переключаемся на указанную ветку

=cut
    sub checkout{
        my ($self,$branch) = @_;
        my $command = $self->{conf}->get("System::whereis_git")." checkout $branch ";
        open(A, "$command|");
        while(<A>){print $_ if $self->{verbose}};
        close(A);
    }
    
=head3 rsync()

синхронизация файлов из репозитория

=cut
    sub rsync{
        my ($self) = @_;
        my $command = $self->{conf}->get("System::whereis_rsync")." -av --progress . ../../.. --exclude .install";
        open(A, "$command|");
        while(<A>){print $_ if $self->{verbose}};
        close(A);
    }
    
1;
