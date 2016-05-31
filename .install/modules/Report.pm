=encoding UTF-8

=head1 Report

Класс для работы с отчетами

=head2 Методы

=cut
package Report;
use Time::localtime;

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
        $self->{data} = '';
        bless $self,$class;
        
        return $self;
    }

=head3 add($data)

    Добавление текста в отчет
    
=item * B<$data> - вывод phpunit в формате TAP

=back
=cut
    sub add{
        my ($self,$data) = @_;
        
        $self->{data} .= $data;
        
    }

    
=head3 create()

    Создание отчета в каталоге
    Возвращает путь к созданному отчету
    
=cut

    sub create{
        my ($self) = @_;
        my $year    = localtime->year()+1900;
        my $mon     = sprintf("%02d",localtime->mon()+1);
        my $day     = sprintf("%02d",localtime->mday());
        my $hour    = sprintf("%02d",localtime->hour());
        my $min     = sprintf("%02d",localtime->min());
        
        my $path = $self->{conf}->get("Report::folder")."/$year-$mon-$day";
        mkdir($path) unless -e $path;
        Dialog::FatalError("Не моду создать каталог для отчетов $path") unless -e $path;

        my $report_name = $path."/report-$year-$mon-$day-$hour-$min.md";
        open(A,">$report_name") or Dialog::FatalError("Не моду создать файл отчета $report_name");
        print A "Отчет по проекту <<".$self->{conf}->get("Project::name").">>\n===\n\n";
        print A $self->{data};
        close(A);
        return $report_name;
    }
    
    
1;
