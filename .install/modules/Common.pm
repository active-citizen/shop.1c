=encoding UTF-8

=head1 Common 

Класс с общими для всех методами и полями

=head2 Методы

=cut
package Common;


=head3 new($conf, $verbose)

=over 4

=item * B<$conf> - ссылка на объект класса Conf (настройки установщика)

=item * B<$verbose> - флаг болтливого режима


=back
=cut 
    sub new{
        
        my ($class, $conf, $verbose) = @_;
        
        my $self = {
            "verbose"       =>  $verbose,
            "conf"          =>  $conf,
            "report"        =>  "",
            "error"         =>  undef
        };
        bless $self,$class;
        
        return $self;
    }

=head3 shell()
    Выполнение команды shell с возвратом результата и, если требуется по verbose
    выводом её выхлопа
    
=over 4

=item * B<$command> - строка для передачи в shell

=item * B<$verbose> - если не указано, используются глобальные настройки, иначе "yes" - выводим выхлоп shell и "no" - не выводим

=back
    
=cut
    sub shell(){
        my ($self,$command,$local_verbose) = @_;
        
        my $verbose = 0;
        $verbose = $self->{verbose} unless $local_verbose;
        $verbose = 1 if $local_verbose eq "yes";
        
        open(A, "$command|");
        my $answer = '';
        while(<A>){
            $answer .= $_;
            print $_ if $verbose;
        }
        chomp($answer);
        close(A);
        return $answer;
    }
    
    
1;
