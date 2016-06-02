=encoding UTF-8

=head1 CodeQuality

Класс для работы с метриками качества кода

=head2 Методы

=cut
package CodeQuality;

use base Common;
use XML::Simple;
use Data::Dumper;

=head3 new($conf, $verbose)

=over 4

=item * B<$conf> - ссылка на объект класса Conf (настройки установщика)

=item * B<$verbose> - флаг болтливого режима


=back
=cut 
    sub new{
        
        my ($class, $conf, $verbose) = @_;

        my $self = Common::new($class, $conf, $verbose);
        $self->{files}      = {};
        $self->{classes}    = {};
        # Информация о пройденных модульных тестах
        bless $self,$class;
        
        return $self;
    }


    
=head3 report($type)

Получить отчет о качестве кода. Отчет делается только для php-файлов

=over 4

=item * B<$type> - тип отчета (pdepend,phpmd). По умолчанию pDepend

=back


=cut 
    sub report{
        my ($self,$type) = @_;
        $type='pdepend' unless $type;
        return $self->pDependReport() if $type eq 'pdepend';
        return $self->phpMD() if $type eq 'phpmd';
    }
    
    
    sub pDependReport{
        my ($self,$type) = @_;
        my $xml_file = $self->{conf}->get("System::temp_dir")."/pdepend.xml";
        
        my $command = $self->{conf}->get("System::pdepend_path")
            ." --summary-xml=".$xml_file
            ." --suffix=".$self->{conf}->get("CodeQuality::file_extensions")
            ." ".$self->{conf}->get("System::temp_dir")."/git";
            
        $self->shell($command);
        Dialog::FatalError("Ошибка создания отчета pdepend") unless -e $xml_file;
        unlink($xml_file);
        
    }
    
1;
