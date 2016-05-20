=encoding UTF-8

=head1 Phantomjs

Класс для работы с Phantomjs

=head2 Методы

=cut
package Phantomjs;

=head3 new($conf, $verbose)

=over 4

=item * B<$conf> - ссылка на объект класса Conf (настройки установщика)

=item * B<$verbose> - флаг болтливого режима


=back
=cut
    sub new{
        
        my ($class, $conf, $verbose) = @_;
        
        my $self = {
            "verbose"   =>  $verbose,
            "conf"      =>  $conf
        };
        bless $self,$class;
        
        
        
        return $self;
    }
    
=head3 generateJS()

Генерация исполняемого кода для phantomjs

=cut
    sub generateJS{
        my ($self) = @_;
        open(A,"js/template.js");
        my $jscode = join("",<A>);
        close(A);
        
        opendir(my $dd,"js");
        my @scripts = ();
        AAA:while(my $filename = readdir($dd)){
            chomp($filename);
            push @scripts,$filename if $filename=~m/(\d+)\-/g;
        }
        @scripts = sort @scripts;
        
        my $datacode = '';
        foreach my $filename(@scripts){
            open(A,"js/".$filename);
            $datacode .= "\ndatas.push(".join("",<A>).");";
            close(A);
        }
        $datacode .= "\n";
        
        $jscode=~s/{Install:data}/$datacode/gm;
        
        my $value = "";
        foreach my $key($jscode=~m/\{\{(.*?)\}\}/gim){
            $value = $self->{conf}->get($key);
            $jscode=~s/\{\{$key\}\}/$value/gim;
        }

        return $jscode;
    }


1;
