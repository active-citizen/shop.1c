=encoding UTF-8

=head1 CodeQuality

Класс для работы с метриками качества кода

=head2 Методы

=cut
package CodeQuality;

use base Common;
use XML::Simple qw(:strict);
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
        $self->{files}      = ();
        $self->{classes}    = ();
        # Информация о пройденных модульных тестах
        bless $self,$class;
        
        return $self;
    }


    
=head3 report($type)

Получить отчет о качестве кода. Отчет делается только для php-файлов

=cut 

    sub report{
        my ($self) = @_;
        
        my $report = "\n\n# Отчет по качеству кода\n\n";
        
        $report .= $self->pDependReport();
        
        $report .= $self->phpMDReport();
        
        return $report;
        
    }
    

=head3 pDependReport($type)

Получить отчет о качестве кода по версии phpmd

=cut 
    
    sub phpMDReport{
        my ($self) = @_;
        my $command = $self->{conf}->get("System::phpmd_path")
            ." ".$self->{conf}->get("System::temp_dir")."/git"
            ." text codesize,unusedcode,naming,design,cleancode";

        my $answer = $self->shell($command);
        
        my @lines = split("\n",$answer);
        chomp(@lines);
        my $root_dir = $self->{conf}->get("System::temp_dir")."/git/";


        my $report = "## По версии phpmd\n\n";
        $report .= "Замечания по коду\n\n";

        AAA:foreach my $line(@lines){
            next AAA unless $line;
            my ($path,$text) = split("\t",$line);
            # вырезаем абсолютный путь к файлу
            $path=~s/$root_dir//gi;
            $report .= $self->metricaLine($path,$text,-inf,inf);
        }
        
        
        return $report;
            
    }
    
    
=head3 pDependReport($type)

Получить отчет о качестве кода по версии pdepend

=cut 
    sub pDependReport{
        my ($self) = @_;
        my $xml_file = $self->{conf}->get("System::temp_dir")."/pdepend.xml";
        # Формируем 
        my $command = $self->{conf}->get("System::pdepend_path")
            ." --summary-xml=".$xml_file
            ." --suffix=".$self->{conf}->get("CodeQuality::file_extensions")
            ." ".$self->{conf}->get("System::temp_dir")."/git";
            
        $self->shell($command);
        Dialog::FatalError("Ошибка создания отчета pdepend") unless -e $xml_file;
        my $xs = XML::Simple->new();
        
        my $xml_tree = $xs->XMLin(
            $xml_file, 
            KeyAttr => {},
            ForceArray => ['method']
        );
        
        # Выбираем информацию по файлам
        push @{$self->{files}}, $_ foreach @{$xml_tree->{files}->{file}};
        
        # Выбираем информацию по классам
        push @{$self->{classes}}, $_ foreach @{$xml_tree->{package}->{class}};
        
        #Удаляем отчет pdepend в xml
        unlink($xml_file);
        
        my $report = "## По версии pdepend\n\n";

        my $metrica = 0;
        
        $report .=  "### Отношение строчек комментариев к исполняемому коду в файлах\n\n";
        $report .=  "Хорошим показателем является значение более 0.5. Если это не выполняется, то, скорее всего, ваш код недостаточно прокомеентирован.\n\n";
        $report .= $self->metricaLine($_->{name},sprintf("%.3f", $_->{cloc}/$_->{eloc}),0.5,inf,1) foreach @{$self->{files}};
        $report .= "\n\n";
        
        my $subreport = '';
        $report .=  "### Число строк в файлах в файлах\n\n";
        $report .=  "Хорошим показателем является значение не более 500. Если это не выполняется, то, скорее всего, код нуждается в рефакторинге.\n\n";
        $subreport .= $self->metricaLine($_->{name},$_->{loc},0,500,1) foreach @{$self->{files}};
        $subreport.= "*Все файлы в пределах нормы*\n" unless $subreport;
        $report .= "$subreport\n";

        $report .=  "### Максимальный уровень вложенности\n\n";
        $report .=  "Хорошим показателем является значение не более 20. Если это не выполняется, то, скорее всего, необходимо некоторые блоки кода выделить в отдельные функции. Далее выводятся только классы, для которых не выполняется\n\n";
        my $subreport = '';
        foreach my $class(@{$self->{classes}}){
            $subreport .= $self->metricaLine($class->{file}->{name}."::".$class->{name}."::".$_->{name},$_->{ccn},0,20,1) foreach @{$class->{method}};
        }
        $subreport.= "*Все классы в пределах нормы*\n" unless $subreport;
        
        $report .= $subreport."\n";
        
        return $report;
        
    }
    
    
=head3 metricaLine($type)

Получить строчку метрики по параметрам. Значение не входящие в отрезок
[$allow_min,$allow_max] выделяются жирным

=over 4

=item * B<$name> - название объекта, для которого формируем метрику
=item * B<$metrica> - значение метрики
=item * B<$allow_min> - минимально допустимое значение метрики
=item * B<$allow_max> - максимально допустимое значение метрики 
=item * B<$hide_if_allow> - скрывать строчку, если значение в рамках допустимого

=back


=cut 
    sub metricaLine{
        my($self, $name, $metrica, $allow_min, $allow_max, $hide_if_allow) = @_;
        
        my $test_metrica = ($metrica<=$allow_max) && ($metrica>=$allow_min);
        
        return '' if $hide_if_allow && $test_metrica;
        
        # Получаем абсолютный путь к временному репозиторию
        my $root_dir = $self->{conf}->get("System::temp_dir")."/git/";
        # вырезаем абсолютный путь к файлу
        $name=~s/$root_dir//gi;
        
        my $line = 
            "* *".$name."* - "
            .(!$test_metrica?"**":"")
            .$metrica
            .(!$test_metrica?"**":"")
            ."\n";
        return $line;
    }
    
    
1;
